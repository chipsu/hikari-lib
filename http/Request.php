<?php

namespace hikari\http;

use \hikari\component\Component;
use \hikari\core\Server;
use \hikari\core\Uri;

class Request extends Component {
    public $uri;
    public $get;
    public $post;
    public $request;

    function __construct(array $parameters = []) {
        if(empty($parameters['uri'])) {
            $parameters['uri'] = new Uri;
        }
        if(empty($parameters['get'])) {
            $parameters['get'] = Server::getParams();
        }
        if(empty($parameters['post'])) {
            $parameters['post'] = Server::postParams();
        }
        if(empty($parameters['request'])) {
            $parameters['request'] = Server::requestParams();
        }
        parent::__construct($parameters);
    }

    function get($key, $default = null) {
        return isset($this->get[$key]) ? $this->get[$key] : $default;
    }

    function post($key, $default = null) {
        return isset($this->post[$key]) ? $this->post[$key] : $default;
    }

    function request($key, $default = null) {
        return isset($this->request[$key]) ? $this->request[$key] : $default;
    }

    function __toString() {
        if($this->uri instanceof Uri) {
            return (string)$this->uri;
        }
        return null;
    }
}
