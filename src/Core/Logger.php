<?php
declare(strict_types=1);

namespace SmartCanteen\Core;

final class Logger
{
    public static function error(string $message): void
    {
        self::write('ERROR', $message);
    }

    public static function info(string $message): void
    {
        self::write('INFO', $message);
    }

    private static function write(string $level, string $message): void
    {
        $dir = APP_ROOT . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $line = sprintf("[%s] %s %s\n", date('Y-m-d H:i:s'), $level, $message);
        if (@file_put_contents($dir . '/app.log', $line, FILE_APPEND | LOCK_EX) === false) {
            error_log($line);
        }
    }
}
