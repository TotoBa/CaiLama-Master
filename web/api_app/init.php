<?php
declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'CaiLama\\WebApi\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

return require __DIR__ . '/config.php';
