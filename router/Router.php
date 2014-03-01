<?php

namespace hikari\router;

class Router extends RouterAbstract {
    function route($request) {
        $path = array_filter(explode('/', trim($request->uri->path, '/')));
        $controller = ucfirst(count($path) ? array_shift($path) : 'index');
        $action = count($path) ? array_shift($path) : 'index';
        $route = new Route([
            'controller' => '\app\controller\\' . $controller,
            'action' => $action,
            'request' => $request,
        ]);
        return $route;
    }
    
    //function build($name, array $parameters = [], $parent = null);
}