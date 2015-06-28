<?php

namespace hikari\core;

class Logger {

    public function getLogLevel() {
        return HI_LOG;
    }

    function getLogFile() {
        return '/tmp/hikari.log';
    }

    function debug() {
        $this->append(HI_LOGLEVEL_DEBUG, func_get_args());
    }

    function trace() {
        $this->append(HI_LOGLEVEL_TRACE, func_get_args());
    }

    function warning() {
        $this->append(HI_LOGLEVEL_WARNING, func_get_args());
    }

    function error() {
        $this->append(HI_LOGLEVEL_ERROR, func_get_args());
    }

    function append($level, array $args) {
        if($level >= $this->getLogLevel()) {
            foreach($args as &$arg) {
                if(is_object($arg)) {
                    $arg = (string)$arg;
                } else if(is_array($arg)) {
                    $arg = preg_replace('/\s*\n\s*/', ' ', print_r($arg, true));
                }
            }
            $message = call_user_func_array('sprintf', $args) . PHP_EOL;
            file_put_contents($this->getLogFile(), $message, FILE_APPEND | LOCK_EX);
        }
    }
}