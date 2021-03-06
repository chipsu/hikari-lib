<?php

namespace hikari\router;

use \hikari\core\Component;
use \hikari\core\Uri;
use \hikari\http\Request;

class RouteGroup extends Component {
    public $name;
    private $_routes = [];
    public $paramMap = [];
    public $defaultParams = [];
    public $propertyFilters = ['propertyFilter'];

    public function propertyFilter(array &$properties) {
        if(!isset($properties['routes'])) {
            $properties['routes'] = [];
        }
        foreach($properties as $key => $value) {
            if(is_numeric($key)) {
                $properties['routes'][] = $value;
                unset($properties[$key]);
            }
        }
    }

    public function getRoutes() {
        return $this->_routes;
    }

    public function setRoutes($routes) {
        foreach($routes as &$route) {
            if(!$route instanceof Route) {
                $route = new Route($route);
            }
        }
        $this->_routes = $routes;
        return $this;
    }

    public function match(Request $request) {
        foreach($this->routes as $route) {
            if($match = $route->match($request)) {
                return $match;
            }
        }
        return false;
    }

    public function build($name, array $parameters) {
        if($name != $this->name) {
            return false;
        }
        foreach($this->routes as $route) {
            if($result = $route->build($parameters)) {
                return $result;
            }
        }
        return false;
    }
}
