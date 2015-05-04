<?php

namespace hikari\router;

use \hikari\core\Component;
use \hikari\core\Uri;
use \hikari\http\Request;

class RouteGroup extends Component {
    public $name;
    private $_routes = [];
    public $controllerMap = [];
    public $propertyFilters = ['parseQuickRoutes'];

    public function parseQuickRoutes(array &$properties) {
        if(!isset($properties['routes'])) {
            $properties['routes'] = [];
        }
        foreach($properties as $key => $value) {
            if(strpos($key, '@') !== false) {
                $methods = ['get', 'post']; #FIX!!!!
                $properties['routes'][] = [
                    'methods' => $methods,
                    'format' => array_shift($value),
                    'params' => $value,
                ];
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
        $this->$this->_routes = $routes;
        return $this;
    }

    public function match(Request $request) {
        foreach($this->routes as $route) {

        }
    }
}
