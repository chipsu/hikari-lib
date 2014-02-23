<?php

namespace hikari\utilities;

use \hikari\component\Component as Component;

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
    
    public function __construct($uri = null) {
        if($uri === null) {
            $https = static::isServerHttps();
            $scheme = $https ? 'https' : 'http';
            $path = explode('?', $_SERVER['REQUEST_URI'], 2);
            $uri = array(
                'scheme' => $scheme,
                'host' => $_SERVER['HTTP_HOST'],
                'port' => $_SERVER['SERVER_PORT'] == static::$defaultPorts[$scheme] ? null : $_SERVER['SERVER_PORT'], 
                'path' => urldecode($path[0]), 
                'query' => isset($path[1]) ? $path[1] : $_SERVER['QUERY_STRING'],
            );
        } else if(is_string($uri)) {
            $uri = parse_url($uri);
        }
        if(!is_array($uri)) {
            \hikari\exception\Argument::raise('Invalid URI "%s"', $uri);
        }
        parent::__construct($uri);
    }
        
    public function __toString() {
        $scheme = $this->scheme ? $this->scheme : 'http';
        $result = $scheme . '://';
        
        if($part = $this->host) {
            $result .= $this->host;
        } else {
            $result .= 'localhost';
        }
        
        if($part = $this->port) {
            if(!isset(static::$defaultPorts[$scheme]) || $port != static::$defaultPorts[$scheme]) {
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
    
    static protected function isServerHttps() {
        if(isset($_SERVER['HTTPS'])) {
            return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        }
        return false;
    }
}
