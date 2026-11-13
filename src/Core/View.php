<?php
declare(strict_types=1);

namespace SmartCanteen\Core;

final class View
{
    /** Render src/Views/<name>.php with $data extracted as local variables. */
    public static function render(string $name, array $data = []): void
    {
        $file = APP_ROOT . '/src/Views/' . $name . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View not found: $name");
        }
        (static function () use ($file, $data): void {
            extract($data, EXTR_SKIP);
            require $file;
        })();
    }
}
