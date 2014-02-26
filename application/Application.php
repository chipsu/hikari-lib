<?php

namespace hikari\application;

class Application extends ApplicationAbstract {
    public $router;
    public $request;

    function run() {
        $this->load('request', [], ['register' => true]);
        $this->load('router', [], ['register' => true]);
        echo $this->request($this->request);
    }

    function request($request) {
        $route = $this->router->route($request);
        $action = $this->load('action', ['id' => $route->action]);
        $controller = $this->load($route->controller, ['application' => $this, 'action' => $action]);
        return $controller->run();
    }

    function forward($request) {
        \hikari\exception\NotImplemented::raise('http redirect?');
    }
}
