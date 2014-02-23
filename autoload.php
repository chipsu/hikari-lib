<?php

// Constants
if(!defined('HI_APP_CONFIG')) define('HI_APP_CONFIG', 'main.php');

// Paths
if(!defined('HI_APP_PATH')) define('HI_APP_PATH', __DIR__ . '/../../app/');

$autoload = function($class) {
    $paths = [HI_APP_PATH . '/../', __DIR__ . '/../'];
    foreach($paths as $path) {
        $file = $path . str_replace('\\', '/', $class) . '.php';
        if(is_file($file)) {
            require_once($file);
            return;
        }
    }
};

$errorHandler = function($code, $message, $filename, $lineno) {
    throw new \ErrorException($message, $code, 1, $filename, $lineno);
};

set_error_handler($errorHandler);
spl_autoload_register($autoload, true, false);
