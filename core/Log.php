<?php

namespace hikari\core;

class Log {
    private static $_logger;

    static function getLogger() {
        if(static::$_logger === null) {
            static::$_logger = new Logger;
        }
        return static::$_logger;
    }

    static function debug() {
        if(HI_LOG_DEBUG) {
            static::getLogger()->append(HI_LOGLEVEL_DEBUG, func_get_args());
        }
    }

    static function trace() {
        if(HI_LOG_TRACE) {
            static::getLogger()->append(HI_LOGLEVEL_TRACE, func_get_args());
        }
    }

    static function warning() {
        if(HI_LOG_WARNING) {
            static::getLogger()->append(HI_LOGLEVEL_WARNING, func_get_args());
        }
    }

    static function error() {
        if(HI_LOG_ERROR) {
            static::getLogger()->append(HI_LOGLEVEL_ERROR, func_get_args());
        }
    }
}
