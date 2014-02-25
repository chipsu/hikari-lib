<?php

require_once __DIR__ . '/autoload/Autoload.php';

use hikari\autoload\Autoload as Autoload;

set_error_handler(function($code, $message, $filename, $lineno) {
    throw new \ErrorException($message, $code, 1, $filename, $lineno);
});

set_exception_handler(function($exception) {
	error_log($exception);
	echo '<pre>';
	echo $exception;
	echo '</pre>';
});

spl_autoload_register(Autoload::$load, true, false);

Autoload::init();
Autoload::push(__DIR__ . '/..');
