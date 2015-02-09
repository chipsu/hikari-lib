<?php

namespace hikari\core;

use hikari\controller\ControllerInterface;
use hikari\exception\Core as CoreException;
use hikari\exception\NotImplemented as NotImplementedException;

class Application extends ApplicationAbstract {
    #public $router;
    #public $request;

    function __construct(array $properties = []) {
        parent::__construct($properties);
        $this->createComponent('router', [], ['register' => true]);
    }

    function run() {
        $this->createComponent('request', [], ['register' => true, 'replace' => true]);
        $event = $this->request();
        if($event->handled) {
            foreach($event->headers as $key => $value) {
                header($key . ': ' . $value);
            }
            echo $event->result;
        }
    }

    function request() {
        $request = $this->router->route($this->request);
        $request->bodyParams = $this->request->bodyParams; // TODO: Not sure if this should be here or in the Router
        $controller = $this->createComponent('controller', [
            'class' => $request->query('controller'),
            'application' => $this,
            'request' => $request,
            'components' => [
                'router' => $this->router, # FIXME
            ],
        ]);
        if(!$controller instanceof ControllerInterface) {
            CoreException::raise('Controller should be an instance of ControllerInterface');
        }
        return $controller->run();
    }

    function forward($request) {
        NotImplementedException::raise('http redirect?');
    }
}
