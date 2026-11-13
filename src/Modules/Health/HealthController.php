<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Health;

use SmartCanteen\Core\Auth;
use SmartCanteen\Core\Database;
use SmartCanteen\Core\Env;
use SmartCanteen\Core\Http;

/**
 * GET health.php
 *  - everyone:            {"status":"ok"|"degraded"}   (safe for uptime monitors)
 *  - admins / APP_ENV!=production: also lists every check and how to fix failures.
 */
final class HealthController
{
    public function show(): void
    {
        $checks = $this->run();
        $failed = array_filter($checks, static fn (array $c): bool => !$c['ok'] && $c['required']);
        $status = $failed ? 'degraded' : 'ok';

        $detailed = Env::get('APP_ENV', 'production') !== 'production' || Auth::role() === 'admin';
        $body = ['status' => $status];
        if ($detailed) {
            $body['checks'] = $checks;
        }
        Http::json($body, $failed ? 503 : 200);
    }

    /** @return array<string,array{ok:bool,required:bool,detail:string}> */
    public function run(): array
    {
        $checks = [];

        $checks['php_version'] = $this->check(version_compare(PHP_VERSION, '8.1.0', '>='), true, 'PHP ' . PHP_VERSION . ' (8.1+ needed)');
        foreach (['mysqli', 'curl', 'mbstring', 'json'] as $ext) {
            $checks['ext_' . $ext] = $this->check(extension_loaded($ext), true, "PHP extension $ext" . (extension_loaded($ext) ? '' : ' is missing - enable it in php.ini'));
        }

        try {
            $db = Database::get();
            $checks['database'] = $this->check(true, true, 'Connected');
            $missing = [];
            foreach (['users', 'menu', 'orders', 'payments', 'ratings', 'login_attempts'] as $table) {
                if ($db->one('SHOW TABLES LIKE ?', 's', [$table]) === null) {
                    $missing[] = $table;
                }
            }
            $checks['tables'] = $this->check(!$missing, true, $missing ? 'Missing: ' . implode(', ', $missing) . ' - run database/schema.sql or the migration' : 'All tables present');

            $cols = [];
            if (!$missing) {
                foreach (['status', 'payment_status', 'usn', 'unit_price', 'payment_id'] as $col) {
                    if ($db->one('SHOW COLUMNS FROM orders LIKE ?', 's', [$col]) === null) {
                        $cols[] = $col;
                    }
                }
            }
            $checks['orders_columns'] = $this->check(!$cols, true, $cols ? 'orders is missing: ' . implode(', ', $cols) . ' - run database/migrations/001_upgrade_existing_db.sql' : 'OK');
        } catch (\Throwable $e) {
            $checks['database'] = $this->check(false, true, 'Cannot connect - check DB_* in .env and that MySQL is running');
        }

        $keyId = (string) Env::get('RAZORPAY_KEY_ID', '');
        $secret = (string) Env::get('RAZORPAY_KEY_SECRET', '');
        $checks['razorpay_keys'] = $this->check(
            $keyId !== '' && $secret !== '' && str_starts_with($keyId, 'rzp_'),
            true,
            $keyId === '' ? 'RAZORPAY_KEY_ID not set' : (str_starts_with($keyId, 'rzp_') ? ($keyId !== '' && str_starts_with($keyId, 'rzp_test_') ? 'Test mode keys' : 'LIVE mode keys') : 'RAZORPAY_KEY_ID should start with rzp_test_ (keys may be swapped)')
        );
        $checks['razorpay_webhook'] = $this->check((string) Env::get('RAZORPAY_WEBHOOK_SECRET', '') !== '', false, 'Optional: lets orders complete if the student closes the tab after paying');
        $checks['mail'] = $this->check((string) Env::get('MAIL_USERNAME', '') !== '' && (string) Env::get('MAIL_PASSWORD', '') !== '', false, 'Optional: order / reset emails are skipped when not configured');
        $checks['logs_writable'] = $this->check(is_writable(APP_ROOT . '/storage/logs'), false, 'storage/logs must be writable');

        return $checks;
    }

    /** @return array{ok:bool,required:bool,detail:string} */
    private function check(bool $ok, bool $required, string $detail): array
    {
        return ['ok' => $ok, 'required' => $required, 'detail' => $detail];
    }
}
