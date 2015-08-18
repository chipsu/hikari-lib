<?php

namespace hikari\http;

use \hikari\core\Component;
use \hikari\core\Server;
use \hikari\core\Uri;

class Request extends Component {
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_HEAD = 'HEAD';

    public $parsers = [];

    private $_uri;
    private $_contentType;
    private $_headers;
    private $_method;
    private $_body;
    private $_bodyParams;
    private $_postParams;
    private $_queryParams;

    function __construct(array $parameters = []) {
        parent::__construct($parameters);
    }

    function getUri() {
        if($this->_uri === null) {
            $this->_uri = new Uri;
        }
        return $this->_uri;
    }

    function getMethod() {
        if($this->_method === null) {
            $this->_method = Server::requestMethod();
        }
        return $this->_method;
    }

    function getContentType() {
        if($this->_contentType === null) {
            $this->_contentType = Server::contentType();
        }
        return $this->_contentType;
    }

    function getHeaders() {
        if($this->_headers === null) {
            $this->_headers = Server::headers();
        }
        return $this->_headers;
    }

    function header($name, $default = null, $strip = false) {
        $headers = $this->getHeaders();
        $result = isset($headers[$name]) ? $headers[$name] : $default;
        if($strip && $result) {
            $result = explode(';', $result);
            $result = explode(',', $result[0]);
        }
        return $result;
    }

    function getBody() {
        if($this->_body === null) {
            $this->_body = file_get_contents('php://input');
        }
        return $this->_body;
    }

    function query($key = null, $default = null) {
        return $key === null ? $this->getQueryParams() : $this->getQueryParam($key, $default);
    }

    function getQueryParams() {
        if($this->_queryParams === null) {
            $this->_queryParams = Server::queryParams();
        }
        return $this->_queryParams;
    }

    function setQueryParams($value) {
        $this->_queryParams = $value;
    }

    function getQueryParam($key, $default = null) {
        $params = $this->getQueryParams();
        return isset($params[$key]) ? $params[$key] : $default;
    }

    /// @deprecated
    function post($key = null, $default = null) {
        return $this->body($key, $default);
    }

    /// @deprecated
    function request($key = null, $default = null) {
        $result = $this->query($key);
        if($result !== null) {
            return $result;
        }
        return $this->body($key, $default);
    }

    function body($key = null, $default = null) {
        return $key === null ? $this->getBodyParams() : $this->getBodyParam($key, $default);
    }

    function getBodyParams() {
        if($this->_bodyParams === null) {
            $contentType = $this->getContentType();
            if(isset($this->parsers[$contentType])) {
                $body = $this->getBody();
                $this->_bodyParams = $this->parsers[$contentType]->parse($body);
            } else if($this->getMethod() === static::METHOD_POST) {
                $this->_bodyParams = $_POST;
            } else {
                $this->_bodyParams = [];
                mb_parse_str($this->getBody(), $this->_bodyParams);
            }
        }
        return $this->_bodyParams;
    }

    function setBodyParams($value) {
        $this->_bodyParams = $value;
    }

    function getBodyParam($key, $default = null) {
        $params = $this->getBodyParams();
        return isset($params[$key]) ? $params[$key] : $default;
    }

    function __toString() {
        try {
            if($this->uri instanceof Uri) {
                return (string)$this->uri;
            }
            return null;
        } catch(\Exception $ex) {
            \hikari\core\Bootstrap::exceptionHandler($ex);
        }
    }
}
