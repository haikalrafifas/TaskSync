<?php

/**
 * Loads core system files.
 */
spl_autoload_register(function ($class) {
    $prefix = 'System\\Core\\';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;

    $relativeClass = substr($class, $len);

    $file = __DIR__ . '/core/' . str_replace('\\', '/', $relativeClass) . '.php';

    !file_exists($file)? :require $file;
});
