<?php

umask(002);

!defined('HI_ENV') && define('HI_ENV', getenv('HI_ENV') ?: 'development');
!defined('HI_DEBUG') && define('HI_DEBUG', getenv('HI_DEBUG') ?: HI_ENV != 'production');
!defined('HI_LOG') && define('HI_LOG', HI_DEBUG);

require_once __DIR__ . '/core/Autoload.php';

use hikari\core\Autoload as Autoload;

$exception_handler = function(\Exception $exception) {
    http_response_code(500);
    error_log($exception);
    if(HI_DEBUG) {
        printf('<pre>%s</pre>', $exception);
    } else {
        printf('Error in %s:%d: %s', basename($exception->getFile()), $exception->getLine(), $exception->getMessage());
    }
};

set_error_handler(function($code, $message, $filename, $lineno) {
    throw new \ErrorException($message, $code, 1, $filename, $lineno);
});

set_exception_handler($exception_handler);

register_shutdown_function(function() use($exception_handler) {
    if($error = error_get_last()) {
        $exception_handler(new \ErrorException($error['message'], $error['type'], 1, $error['file'], $error['line']));
    }
});

spl_autoload_register(Autoload::$load, true, false);

Autoload::init();
Autoload::push(__DIR__ . '/..');
