<?php

namespace hikari\router;

use \hikari\component\Component as Component;

abstract class RouterAbstract extends Component implements RouterInterface {
    public $routes = [];

    function __construct(array $properties = []) {
        parent::__construct($properties);
        if($this->application->config->hash) {
            // TODO: Cache
        }
        foreach($this->routes as $name => $route) {
            if(!$route instanceof Route) {
                empty($route['name']) and $route['name'] = $name;
                $this->routes[$name] = new Route($route);
            }
        }
    }

    function route($request) {
        foreach($this->routes as $route) {
            if($match = $route->match($request)) {
                return $match;
            }
        }
        \hikari\exception\NotFound::raise();
    }
    
    //function build($name, array $parameters = [], $parent = null);
}
