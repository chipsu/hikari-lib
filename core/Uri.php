<?php

namespace hikari\core;

use \hikari\exception\Argument;

class Uri extends Component {
    public static $defaultPorts = array(
        'http' => 80,
        'https' => 443,
    );
    public $scheme;
    public $host;
    public $port;
    public $path;
    public $query;
    public $fragment;

    function __construct($uri = null) {
        if($uri === null) {
            $https = Server::https();
            $scheme = $https ? 'https' : 'http';
            $path = explode('?', Server::requestUri(), 2);
            $uri = array(
                'scheme' => $scheme,
                'host' => Server::host(),
                'port' => Server::port() == static::$defaultPorts[$scheme] ? null : Server::port(),
                'path' => urldecode($path[0]),
                'query' => isset($path[1]) ? $path[1] : Server::queryString(),
            );
        } else if(is_string($uri)) {
            $uri = parse_url($uri);
        }
        if(!is_array($uri)) {
            Argument::raise('Invalid URI "%s"', $uri);
        }
        if(is_array($uri['query'])) {
            $uri['query'] = http_build_query($uri['query']);
        }
        parent::__construct($uri);
    }

    function __toString() {
        $scheme = $this->scheme ? $this->scheme : 'http';
        $result = $scheme . '://';

        if($part = $this->host) {
            $result .= $part;
        } else {
            $result .= Server::host();
        }

        if($part = $this->port) {
            if(!isset(static::$defaultPorts[$scheme]) || $part != static::$defaultPorts[$scheme]) {
                $result .= ':' . $part;
            }
        }

        if($part = $this->path) {
            $result .= $part[0] === '/' ? $part : '/' . $part;
        }

        if($part = $this->query) {
            $result .= '?' . $part;
        }

        if($part = $this->fragment) {
            $result .= '#' . $part;
        }

        return $result;
    }
}
