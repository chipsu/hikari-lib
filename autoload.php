<?php

umask(002);

!defined('HI_ENV') && define('HI_ENV', getenv('HI_ENV') ?: 'development');
!defined('HI_DEBUG') && define('HI_DEBUG', getenv('HI_DEBUG') ?: HI_ENV != 'production');
!defined('HI_LOG') && define('HI_LOG', HI_DEBUG);

require_once __DIR__ . '/autoload/Autoload.php';

use hikari\autoload\Autoload as Autoload;

set_error_handler(function($code, $message, $filename, $lineno) {
    throw new \ErrorException($message, $code, 1, $filename, $lineno);
});

set_exception_handler(function($exception) {
    error_log($exception);
    http_response_code(500);
    if(HI_DEBUG) {
        echo '<pre>';
        echo $exception;
        echo '</pre>';
    } else {
        echo $exception->getMessage();
    }
});

spl_autoload_register(Autoload::$load, true, false);

Autoload::init();
Autoload::push(__DIR__ . '/..');
