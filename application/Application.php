<?php

namespace hikari\application;

class Application extends ApplicationAbstract {
    public $router;
    public $request;

    function run() {
        // Rewrite fix
        if(empty($_GET) && ($pos = strpos($_SERVER['REQUEST_URI'], '?')) !== false) {
            $query = substr($_SERVER['REQUEST_URI'], $pos + 1);
            parse_str($query, $_GET);
            $_REQUEST = array_merge($_GET, $_REQUEST);
        }
        $this->load('request', [], ['register' => true]);
        $this->load('router', [], ['register' => true]);
        echo $this->request($this->request);
    }

    function request($request) {
        $route = $this->router->route($request);
        $action = $this->load('action', ['id' => $route->action]);
        $controller = $this->load($route->controller, [
            'application' => $this,
            'action' => $action,
            'request' => $route->request,
        ]);
        return $controller->run();
    }

    function forward($request) {
        \hikari\exception\NotImplemented::raise('http redirect?');
    }
}
