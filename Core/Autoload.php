<?php

spl_autoload_register(function ($class) {
    $root = dirname(__DIR__);
    $class_path = str_replace('\\', '/', $class);
    $file = "{$root}/{$class_path}.php";
    if (is_readable($file)) {
        require $file;
    }
});