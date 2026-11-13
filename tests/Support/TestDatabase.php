<?php
declare(strict_types=1);

namespace SmartCanteen\Tests\Support;

use mysqli;
use SmartCanteen\Core\Database;
use SmartCanteen\Core\Env;

/** Creates / resets the throw-away test database. Refuses to run against anything not named *_test. */
final class TestDatabase
{
    private static ?bool $ready = null;

    public static function boot(): bool
    {
        if (self::$ready !== null) {
            return self::$ready;
        }
        $name = (string) getenv('DB_NAME');
        if (!str_ends_with($name, '_test')) {
            throw new \RuntimeException("Refusing to run tests against database '$name' (name must end with _test).");
        }

        try {
            $c = new mysqli(
                Env::get('DB_HOST', 'localhost'),
                Env::get('DB_USER', 'root'),
                Env::get('DB_PASS', ''),
                '',
                (int) Env::get('DB_PORT', '3306')
            );
            $c->query("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $c->select_db($name);

            $exists = $c->query("SHOW TABLES LIKE 'login_attempts'")->num_rows > 0;
            if (!$exists) {
                $schema = (string) file_get_contents(APP_ROOT . '/database/schema.sql');
                $schema = str_replace('`smartcanteen`', "`$name`", $schema);
                $c->multi_query($schema);
                do {
                    $r = $c->store_result();
                    if ($r instanceof \mysqli_result) {
                        $r->free();
                    }
                } while ($c->more_results() && $c->next_result());
            }
            $c->close();
            self::$ready = true;
        } catch (\Throwable $e) {
            fwrite(STDERR, "\n[tests] Database not available, integration tests will be skipped: " . $e->getMessage() . "\n");
            self::$ready = false;
        }
        return self::$ready;
    }

    public static function reset(): void
    {
        $db = Database::get();
        foreach (['ratings', 'login_attempts', 'orders', 'payments', 'menu', 'users'] as $table) {
            $db->run("DELETE FROM `$table`");
        }
    }
}
