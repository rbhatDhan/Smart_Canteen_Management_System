<?php
declare(strict_types=1);

/**
 * Application bootstrap: autoloader, config, error handling, session.
 * Every public entry-point file starts with: require __DIR__ . '/src/bootstrap.php';
 */

define('APP_ROOT', dirname(__DIR__));

require __DIR__ . '/autoload.php';


use SmartCanteen\Core\Env;
use SmartCanteen\Core\Logger;
use SmartCanteen\Core\Session;

Env::load(APP_ROOT . '/.env');
date_default_timezone_set(Env::get('APP_TIMEZONE', 'Asia/Kolkata'));

$debug = Env::get('APP_ENV', 'production') !== 'production';
ini_set('display_errors', $debug ? '1' : '0');
error_reporting(E_ALL);

set_exception_handler(static function (Throwable $e) use ($debug): void {
    Logger::error($e::class . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo $debug
        ? '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>'
        : 'Something went wrong. Please try again later.';
});

Session::start();
