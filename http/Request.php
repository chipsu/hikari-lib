<?php

namespace hikari\http;

use \hikari\component\Component as Component;
use \hikari\utilities\Uri as Uri;

class Request extends Component {
    public $uri;
    public $get;
    public $post;
    public $request;
    
    public function __construct(array $parameters = []) {
        if(empty($parameters['uri'])) {
            $parameters['uri'] = new Uri;
        }
        if(empty($parameters['get'])) {
            $parameters['get'] = $_GET;
        }
        if(empty($parameters['post'])) {
            $parameters['post'] = $_POST;
        }
        if(empty($parameters['request'])) {
            $parameters['request'] = $_REQUEST;
        }
        parent::__construct($parameters);
    }
    
    public function get($key, $default = null) {
        return isset($this->get[$key]) ? $this->get[$key] : $default;
    }
    
    public function post($key, $default = null) {
        return isset($this->post[$key]) ? $this->post[$key] : $default;
    }
    
    public function request($key, $default = null) {
        return isset($this->request[$key]) ? $this->request[$key] : $default;
    }
    
    public function __toString() {
        return (string)$this->uri;
    }
}
