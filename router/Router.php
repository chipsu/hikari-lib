<?php

namespace hikari\router;

class Router extends RouterAbstract {
    function route($request) {
        $path = array_filter(explode('/', trim($request->uri->path, '/')));
        $controller = ucfirst(count($path) ? array_shift($path) : 'index');
        $action = count($path) ? array_shift($path) : 'index';
        $route = new Route(['controller' => '\app\controller\\' . $controller, 'action' => $action]);
        // TODO: Return new Request instead?
        // or an Action? (with controller + request, or controller + action in request?)
        return $route;
    }
    
    //function build($name, array $parameters = [], $parent = null);
}