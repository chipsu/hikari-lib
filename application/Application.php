<?php

namespace hikari\application;

class Application extends ApplicationAbstract {
    public $router;
    public $request;

    function __construct(array $properties = []) {
        parent::__construct($properties);
        $this->load('router', [], ['register' => true]);
    }

    function run() {
        $this->load('request', [], ['register' => true, 'replace' => true]);
        echo $this->request();
    }

    function request() {
        $request = $this->router->route($this->request);
        $action = $this->load('action', ['id' => $request->get('action')]);
        $controller = $this->load($request->get('controller'), [
            'application' => $this,
            'action' => $action,
            'request' => $request,
        ]);
        if(!$controller instanceof \hikari\controller\ControllerInterface) {
            \hikari\core\Exception::raise();
        }
        return $controller->run();
    }

    function forward($request) {
        \hikari\exception\NotImplemented::raise('http redirect?');
    }
}
