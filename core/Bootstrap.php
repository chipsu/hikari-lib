<?php

namespace hikari\core;

class Bootstrap {
    public static $errorHandler;
    public static $exceptionHandler;
    public static $shutdownFunction;
    private static $init = false;

    static function app(array $properties) {
        static::init();
        assert(is_dir($properties['path']));
        $name = isset($properties['name']) ? $properties['name'] : basename($properties['path']);
        $class = isset($properties['class']) ? $properties['class'] : '\\' . $name .'\core\Application';
        if(!class_exists($class)) {
            $class = '\hikari\core\Application';
        }
        $app = new $class($properties);
        return $app;
    }

    static function run(array $properties) {
        $app = static::app($properties);
        return $app->run();
    }

    static function init() {
        if(!static::$init) {
            static::initCore();
            static::initLogging();
            static::initErrorHandling();
            static::$init = true;
        }
    }

    static function initCore() {
        error_reporting(E_ALL);

        umask(002);

        !defined('HI_ENV') and define('HI_ENV', getenv('HI_ENV') ?: 'development');
        !defined('HI_DEBUG') and define('HI_DEBUG', getenv('HI_DEBUG') ?: HI_ENV != 'production');
    }

    static function initLogging() {
        define('HI_LOGLEVEL_DEBUG',     1000);
        define('HI_LOGLEVEL_TRACE',     2000);
        define('HI_LOGLEVEL_WARNING',   5000);
        define('HI_LOGLEVEL_ERROR',     9999);

        !defined('HI_LOG') and define('HI_LOG', HI_DEBUG ? HI_LOGLEVEL_DEBUG : HI_LOGLEVEL_WARNING);
        !defined('HI_LOG_DEBUG') and define('HI_LOG_DEBUG', HI_LOG <= HI_LOGLEVEL_DEBUG);
        !defined('HI_LOG_TRACE') and define('HI_LOG_TRACE', HI_LOG <= HI_LOGLEVEL_TRACE);
        !defined('HI_LOG_WARNING') and define('HI_LOG_WARNING', HI_LOG <= HI_LOGLEVEL_WARNING);
        !defined('HI_LOG_ERROR') and define('HI_LOG_ERROR', HI_LOG <= HI_LOGLEVEL_ERROR);
    }

    static function initErrorHandling() {
        if(!static::$exceptionHandler) {
            static::$exceptionHandler = [__CLASS__, 'defaultExceptionHandler'];
        }
        set_exception_handler(static::$exceptionHandler);

        if(!static::$errorHandler) {
            static::$errorHandler = [__CLASS__, 'defaultErrorHandler'];
        }
        set_error_handler(static::$errorHandler);

        if(!static::$shutdownFunction) {
            static::$shutdownFunction = [__CLASS__, 'defaultShutdownFunction'];
        }
        register_shutdown_function(static::$shutdownFunction);
    }

    static function exceptionHandler(\Exception $exception, $die = true) {
        call_user_func(static::$exceptionHandler, $exception);
        if($die) {
            die;
        }
    }

    static function defaultExceptionHandler(\Exception $exception) {
        if(!headers_sent()) {
            http_response_code(500);
        }
        error_log($exception);
        $accept = isset($_SERVER['HTTP_ACCEPT']) ? explode(',', $_SERVER['HTTP_ACCEPT']) : ['text/plain'];
        $error = [
            'file' => basename($exception->getFile()),
            'line' => $exception->getLine(),
            'message' => $exception->getMessage(),
        ];
        if(HI_DEBUG) {
            $error['summary'] = (string)$exception;
            $error['trace'] = [];
            foreach($exception->getTrace() as $trace) {
                $error['trace'][] = $trace;
            }
        } else {
            $error['summary'] = sprintf(
                '%3$s in %1$s on line %2$d',
                $error['file'], $error['line'], $error['message']
            );
        }
        switch($accept[0]) {
        case 'text/html':
            if(!headers_sent()) {
                header('Content-Type: text/html');
            }
            echo '<!DOCTYPE HTML>';
            echo '<html><body>';
            echo '<h1>Error in application</h1>';
            if(HI_DEBUG) {
                echo '<pre style="width:100%;white-space:pre-wrap">';
                var_dump($error['summary']);
                echo '</pre>';
            } else {
                echo '<p>' . $error['summary'] . '</p>';
            }
            echo '</body></html>';
            break;
        case 'application/json':
            if(!headers_sent()) {
                header('Content-Type: application/json');
            }
            echo json_encode(['$error' => $error]);
            break;
        case 'text/plain':
        default:
            if(!headers_sent()) {
                header('Content-Type: text/plain');
            }
            echo $error['summary'];
            break;
        }
    }

    static function defaultErrorHandler($code, $message, $filename, $lineno) {
        throw new \ErrorException($message, $code, 1, $filename, $lineno);
    }

    static function defaultShutdownFunction() {
        if($error = error_get_last()) {
            call_user_func(static::$exceptionHandler, new \ErrorException(
                $error['message'], $error['type'], 1, $error['file'], $error['line']
            ));
        }
    }
}
