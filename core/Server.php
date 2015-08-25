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
        return isset($_SERVER['HTTP_HOST']) ? preg_replace('/\:.*/', '', $_SERVER['HTTP_HOST']) : 'localhost';
    }

    static function port() {
        if(isset($_SERVER['HTTP_HOST'])) {
            if(preg_match('/\:(\d+)/', $_SERVER['HTTP_HOST'], $match)) {
                return $match[1];
            }
        }
        return isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80;
    }

    static function queryString() {
        return isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
    }

    static function requestUri() {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    }

    static function contentType() {
        return isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    }

    static function requestMethod() {
        return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
    }

    static function headers() {
        if(function_exists('http_get_request_headers')) {
            $result = \http_get_request_headers();
        } else if(function_exists('getallheaders')) {
            // I have no idea, the following code results in undefined index:
            // $x = getallheaders(); foreach($x as $k => $v) { echo $x[$k]; }
            // array_change_key_case below seems to fix it..
            $result = getallheaders();
        } else {
            $result = [];
            foreach($_SERVER as $key => $value) {
                if(substr($key, 0, 5) === 'HTTP_') {
                    $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                    $result[$name] = $value;
                }
            }
        }
        return array_change_key_case($result, \CASE_LOWER);
    }

    static function https() {
        if(isset($_SERVER['HTTPS'])) {
            return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        }
        return false;
    }

    static function referer() {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    }

    protected static function rewriteFix() {
        static $fixed = false;
        if($fixed === false) {
            if(empty($_GET) && ($uri = static::requestUri()) && ($pos = strpos($uri, '?')) !== false) {
                $query = substr($uri, $pos + 1);
                parse_str($query, $_GET);
                $_REQUEST = array_merge($_GET, $_REQUEST);
            }
            $fixed = true;
        }
    }
}
