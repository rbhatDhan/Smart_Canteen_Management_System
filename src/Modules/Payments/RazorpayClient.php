<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Payments;

use SmartCanteen\Core\Env;

/**
 * Minimal Razorpay REST client (Orders / Payments) using cURL - no Composer SDK needed.
 * Works with test keys (rzp_test_...) exactly like live keys.
 */
class RazorpayClient
{
    private const API = 'https://api.razorpay.com/v1';

    private string $keyId;
    private string $keySecret;

    public function __construct()
    {
        $this->keyId     = (string) Env::get('RAZORPAY_KEY_ID', '');
        $this->keySecret = (string) Env::get('RAZORPAY_KEY_SECRET', '');
    }

    public function configured(): bool
    {
        return $this->keyId !== '' && $this->keySecret !== '';
    }

    public function keyId(): string
    {
        return $this->keyId;
    }

    public function isTestMode(): bool
    {
        return str_starts_with($this->keyId, 'rzp_test_');
    }

    /** @return array<string,mixed> Razorpay order entity (id, amount, currency, ...) */
    public function createOrder(int $amountPaise, string $receipt, array $notes = []): array
    {
        return $this->request('POST', '/orders', [
            'amount'   => $amountPaise,
            'currency' => 'INR',
            'receipt'  => $receipt,
            'notes'    => $notes,
        ]);
    }

    public function fetchPayment(string $paymentId): array
    {
        return $this->request('GET', '/payments/' . rawurlencode($paymentId));
    }

    public function capturePayment(string $paymentId, int $amountPaise): array
    {
        return $this->request('POST', '/payments/' . rawurlencode($paymentId) . '/capture', [
            'amount'   => $amountPaise,
            'currency' => 'INR',
        ]);
    }

    /** Checkout callback signature: HMAC_SHA256(order_id|payment_id, key_secret). */
    public function verifyCheckoutSignature(string $orderId, string $paymentId, string $signature): bool
    {
        if (!$this->configured() || $signature === '') {
            return false;
        }
        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
        return hash_equals($expected, $signature);
    }

    /** Webhook signature: HMAC_SHA256(raw_body, webhook_secret). */
    public function verifyWebhookSignature(string $rawBody, string $signature): bool
    {
        $secret = (string) Env::get('RAZORPAY_WEBHOOK_SECRET', '');
        if ($secret === '' || $signature === '') {
            return false;
        }
        return hash_equals(hash_hmac('sha256', $rawBody, $secret), $signature);
    }

    /** @throws \RuntimeException on transport or API errors */
    private function request(string $method, string $path, ?array $body = null): array
    {
        if (!$this->configured()) {
            throw new \RuntimeException('Razorpay keys are not configured.');
        }

        $ch = curl_init(self::API . $path);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => $this->keyId . ':' . $this->keySecret,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ];
        // XAMPP on Windows often has no CA bundle; point CURL_CA_BUNDLE at cacert.pem in .env if needed.
        $ca = Env::get('CURL_CA_BUNDLE');
        if ($ca) {
            $opts[CURLOPT_CAINFO] = $ca;
        }
        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($body ?? new \stdClass());
        }
        curl_setopt_array($ch, $opts);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('Could not reach Razorpay: ' . $err);
        }
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode((string) $raw, true);
        if (!is_array($data)) {
            throw new \RuntimeException("Unexpected Razorpay response (HTTP $status).");
        }
        if ($status >= 400) {
            $desc = $data['error']['description'] ?? 'unknown error';
            throw new \RuntimeException("Razorpay API error (HTTP $status): $desc");
        }
        return $data;
    }
}
