<?php

umask(002);

!defined('HI_ENV') and define('HI_ENV', getenv('HI_ENV') ?: 'development');
!defined('HI_DEBUG') and define('HI_DEBUG', getenv('HI_DEBUG') ?: HI_ENV != 'production');

// Logging constants

define('HI_LOGLEVEL_DEBUG',     1000);
define('HI_LOGLEVEL_TRACE',     2000);
define('HI_LOGLEVEL_WARNING',   5000);
define('HI_LOGLEVEL_ERROR',     9999);

!defined('HI_LOG') and define('HI_LOG', HI_DEBUG ? HI_LOG_DEBUG : HI_LOG_WARNING);
!defined('HI_LOG_DEBUG') and define('HI_LOG_DEBUG', HI_LOG <= HI_LOGLEVEL_DEBUG);
!defined('HI_LOG_TRACE') and define('HI_LOG_TRACE', HI_LOG <= HI_LOGLEVEL_TRACE);
!defined('HI_LOG_WARNING') and define('HI_LOG_WARNING', HI_LOG <= HI_LOGLEVEL_WARNING);
!defined('HI_LOG_ERROR') and define('HI_LOG_ERROR', HI_LOG <= HI_LOGLEVEL_ERROR);

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
