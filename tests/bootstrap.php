<?php
declare(strict_types=1);

/**
 * PHPUnit bootstrap. Loads the app autoloader WITHOUT starting a session, and points the
 * database at a separate *_test database so real data is never touched.
 */
define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/src/autoload.php';
require __DIR__ . '/Support/TestDatabase.php';
require __DIR__ . '/Support/FakeRazorpay.php';
require __DIR__ . '/Support/DbTestCase.php';

SmartCanteen\Core\Env::load(APP_ROOT . '/.env'); // for DB host/user/password only

// Values below win over .env (Env::get prefers real environment variables).
putenv('DB_NAME=' . (getenv('TEST_DB_NAME') ?: 'smartcanteen_test'));
putenv('RAZORPAY_KEY_ID=rzp_test_unit');
putenv('RAZORPAY_KEY_SECRET=unit_test_secret');
putenv('RAZORPAY_WEBHOOK_SECRET=unit_webhook_secret');
putenv('MAIL_ENABLED=false');
date_default_timezone_set('Asia/Kolkata');
