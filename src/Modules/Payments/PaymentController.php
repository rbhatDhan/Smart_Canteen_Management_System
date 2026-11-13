<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Payments;

use SmartCanteen\Core\Auth;
use SmartCanteen\Core\Csrf;
use SmartCanteen\Core\Http;
use SmartCanteen\Core\Logger;

/** JSON endpoints behind api/payments_*.php and api/razorpay_webhook.php */
final class PaymentController
{
    private PaymentService $service;

    public function __construct()
    {
        $this->service = new PaymentService();
    }

    private function guardStudentPost(): void
    {
        Auth::requireRoleJson('student');
        if (!Http::isPost()) {
            Http::json(['success' => false, 'message' => 'Method not allowed.'], 405);
        }
        if (!Csrf::valid()) {
            Http::json(['success' => false, 'message' => 'Session expired. Please refresh the page.'], 419);
        }
    }

    public function create(): void
    {
        $this->guardStudentPost();
        $items = Http::jsonBody()['items'] ?? [];
        try {
            $checkout = $this->service->createCheckout(Auth::id(), is_array($items) ? $items : []);
            Http::json(['success' => true] + $checkout);
        } catch (\InvalidArgumentException $ex) {
            Http::json(['success' => false, 'message' => $ex->getMessage()], 422);
        } catch (\Throwable $ex) {
            Logger::error('create payment: ' . $ex->getMessage());
            Http::json(['success' => false, 'message' => 'Payments are temporarily unavailable. Please try again shortly.'], 502);
        }
    }

    public function verify(): void
    {
        $this->guardStudentPost();
        $b = Http::jsonBody();
        try {
            $result = $this->service->confirm(
                Auth::id(),
                (string) ($b['razorpay_order_id'] ?? ''),
                (string) ($b['razorpay_payment_id'] ?? ''),
                (string) ($b['razorpay_signature'] ?? '')
            );
            Http::json($result, $result['success'] ? 200 : 400);
        } catch (\Throwable $ex) {
            Logger::error('verify payment: ' . $ex->getMessage());
            Http::json(['success' => false, 'message' => 'Could not confirm the payment. If money was debited it will be reconciled automatically.'], 500);
        }
    }

    public function failed(): void
    {
        $this->guardStudentPost();
        $b = Http::jsonBody();
        $orderId = (string) ($b['order_id'] ?? '');
        if (preg_match('/^order_\w+$/', $orderId)) {
            $pid = (string) ($b['payment_id'] ?? '');
            $this->service->recordFailure(
                $orderId,
                preg_match('/^pay_\w+$/', $pid) ? $pid : null,
                (string) ($b['reason'] ?? 'Payment failed')
            );
        }
        Http::json(['success' => true]);
    }

    /** Razorpay -> server notification. Must be configured in the Razorpay dashboard. */
    public function webhook(): void
    {
        $raw = (string) file_get_contents('php://input');
        $sig = (string) ($_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '');
        if (!(new RazorpayClient())->verifyWebhookSignature($raw, $sig)) {
            Http::json(['success' => false], 400);
        }

        $event = json_decode($raw, true) ?: [];
        $type  = (string) ($event['event'] ?? '');
        $pay   = $event['payload']['payment']['entity'] ?? [];
        $orderId = (string) ($pay['order_id'] ?? ($event['payload']['order']['entity']['id'] ?? ''));
        $payId   = (string) ($pay['id'] ?? '');

        try {
            if (in_array($type, ['order.paid', 'payment.captured'], true) && $orderId !== '' && $payId !== '') {
                $this->service->confirmFromWebhook($orderId, $payId);
            } elseif ($type === 'payment.failed' && $orderId !== '') {
                $this->service->recordFailure($orderId, $payId ?: null, (string) ($pay['error_description'] ?? 'Payment failed'));
            }
        } catch (\Throwable $ex) {
            Logger::error('webhook ' . $type . ': ' . $ex->getMessage());
            Http::json(['success' => false], 500); // Razorpay will retry
        }
        Http::json(['success' => true]);
    }
}
