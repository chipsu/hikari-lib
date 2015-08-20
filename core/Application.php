<?php

namespace hikari\core;

use hikari\controller\ControllerInterface;
use hikari\exception\Core as CoreException;
use hikari\exception\NotImplemented as NotImplementedException;

class Application extends ApplicationAbstract {
    private $_router;
    private $_request;

    function __construct(array $properties = []) {
        parent::__construct($properties);
    }

    function getRouter() {
        if($this->_router === null) {
            $this->_router = $this->createComponent('router');
        }
        return $this->_router;
    }

    function getRequest() {
        if($this->_request === null) {
            $this->_request = $this->createComponent('request');
        }
        return $this->_request;
    }

    function run() {
        try {
            $event = $this->handleRequest($this->request);
        } catch(\Exception $exception) {
            $errorRequest = new \hikari\http\Request([
                'queryParams' => [
                    'controller' => 'error',
                    'exception' => $exception,
                ],
            ]);
            try {
                $event = $this->handleRequest($errorRequest);
            } catch(\Exception $innerException) {
                // TODO: Log error route fail
                throw $exception;
            }
        }
        if($event->handled) {
            foreach($event->headers as $key => $value) {
                header($key . ': ' . $value);
            }
            echo $event->result;
        }
    }

    function forward($request) {
        return $this->handleRequest($request);
    }

    protected function handleRequest($request) {
        $routedRequest = $this->router->route($request);
        $routedRequest->bodyParams = $request->bodyParams; // TODO: Not sure if this should be here or in the Router
        $controller = $this->createComponent('controller', [
            'class' => $routedRequest->query('controller'),
            'application' => $this,
            'request' => $routedRequest,
            'components' => [
                'router' => $this->router, # FIXME
            ],
        ]);
        if(!$controller instanceof ControllerInterface) {
            CoreException::raise('Controller should be an instance of ControllerInterface');
        }
        return $controller->run();
    }
}
