<?php
declare(strict_types=1);

/**
 * Prepares the throw-away end-to-end database (default: smartcanteen_e2e).
 * Run automatically by Playwright's global setup; can also be run by hand:
 *     php tests/e2e/setup_e2e_db.php
 * It WIPES the e2e database every run and creates one admin: e2e_admin / E2e-Admin-Pass1
 */
define('APP_ROOT', dirname(__DIR__, 2));
require APP_ROOT . '/src/autoload.php';

use SmartCanteen\Core\Env;

Env::load(APP_ROOT . '/.env');
$name = getenv('E2E_DB_NAME') ?: 'smartcanteen_e2e';
if (!str_ends_with($name, '_e2e')) {
    fwrite(STDERR, "Refusing to touch '$name': the e2e database name must end with _e2e\n");
    exit(1);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$c = new mysqli(
    Env::get('DB_HOST', 'localhost'),
    Env::get('DB_USER', 'root'),
    Env::get('DB_PASS', ''),
    '',
    (int) Env::get('DB_PORT', '3306')
);
$c->query("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
$c->select_db($name);

if ($c->query("SHOW TABLES LIKE 'login_attempts'")->num_rows === 0) {
    $schema = str_replace('`smartcanteen`', "`$name`", (string) file_get_contents(APP_ROOT . '/database/schema.sql'));
    $c->multi_query($schema);
    do {
        $r = $c->store_result();
        if ($r instanceof mysqli_result) {
            $r->free();
        }
    } while ($c->more_results() && $c->next_result());
}

foreach (['ratings', 'login_attempts', 'orders', 'payments', 'menu', 'users'] as $table) {
    $c->query("DELETE FROM `$table`");
}

$hash = password_hash('E2e-Admin-Pass1', PASSWORD_DEFAULT);
$stmt = $c->prepare("INSERT INTO users (username, password, role) VALUES ('e2e_admin', ?, 'admin')");
$stmt->bind_param('s', $hash);
$stmt->execute();

echo "e2e database '$name' is ready (admin: e2e_admin)\n";
