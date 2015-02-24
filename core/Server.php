<?php

namespace hikari\core;

class Server {
    static $headers;

    static function queryParams() {
        static::rewriteFix();
        return $_GET;
    }

    static function postParams() {
        return $_POST;
    }

    static function requestParams() {
        static::rewriteFix();
        return $_REQUEST;
    }

    static function host() {
        return $_SERVER['HTTP_HOST'];
    }

    static function port() {
        return $_SERVER['SERVER_PORT'];
    }

    static function queryString() {
        return $_SERVER['QUERY_STRING'];
    }

    static function requestUri() {
        return $_SERVER['REQUEST_URI'];
    }

    static function contentType() {
        return $_SERVER['CONTENT_TYPE'];
    }

    static function requestMethod() {
        return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
    }

    static function headers() {
        if(static::$headers === null) {
            static::$headers = http_get_request_headers();
        }
        return static::$headers;
    }

    static function https() {
        if(isset($_SERVER['HTTPS'])) {
            return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        }
        return false;
    }

    static function referer() {
        return $_SERVER['HTTP_REFERER'];
    }

    protected static function rewriteFix() {
        static $fixed = false;
        if($fixed === false) {
            if(empty($_GET) && ($pos = strpos($_SERVER['REQUEST_URI'], '?')) !== false) {
                $query = substr($_SERVER['REQUEST_URI'], $pos + 1);
                parse_str($query, $_GET);
                $_REQUEST = array_merge($_GET, $_REQUEST);
            }
            $fixed = true;
        }
    }
}