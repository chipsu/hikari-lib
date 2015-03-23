<?php

namespace hikari\controller;

use \hikari\core\Component;
use \hikari\core\Event;

abstract class ControllerAbstract extends Component implements ControllerInterface {
    public $action;
    public $actions = [];
    public $request;
    public $view;
    public $id;
    private $_router;
    private $_viewFile;

    function __construct(array $properties = []) {
        parent::__construct($properties);
        if(empty($this->id)) {
            $class = get_class($this);
            $class = explode('\\', $class);
            $this->id = strtolower(array_pop($class));
        }
    }

    function run() {
        $this->action = $this->createAction();
        $event = new Event(['controller' => $this, 'action' => $this->action]);

        if($this->beforeAction($event)) {
            $event->result = $this->action->invoke();
            $this->afterAction($event);
        }
        return $event;
    }

    function getRouter() {
        if($this->_router === null) {
            $this->_router = $this->createComponent('router', [/*'context' => $this->getContext()*/]);
        }
        return $this->_router;
    }

    function setRouter($value) {
        $this->_router = $value;
    }

    /**
     * @todo Base Controller should not be aware of the View, ViewInterface or some clever event.
     */
    function getViewFile() {
        if($this->_viewFile === null && $this->action) {
            $this->_viewFile = $this->id . '/' . $this->action->id;
        }
        return $this->_viewFile;
    }

    function setViewFile($value) {
        $this->_viewFile = $value;
    }

    /**
     * @todo Same as viewFile, controller should not be directly aware of this
     */
    function beforeRender($event) {
        return $this->trigger(__FUNCTION__, $event);
    }

    function afterRender($event) {
        return $this->trigger(__FUNCTION__, $event);
    }

    protected function createAction() {
        $id = $this->request->query('action');
        $action = isset($this->actions[$id])
                ? $this->actions[$id]
                : $this->config->get('actionPrefix', '') . $id;

        if($action instanceof ActionInterface) {
            return $action;
        }

        if(is_string($action)) {
            $method = new \ReflectionMethod($this, $action);
            return new Action(['id' => $id, 'context' => $this, 'method' => $method]);
        } else if(is_callable($action)) {
            $method = new \ReflectionFunction($action);
            return new Action(['id' => $id, 'context' => $this, 'method' => $method]);
        }

        \hikari\exception\Core::raise('Action "%s" is not a method name or callback', $action);
    }

    protected function beforeAction($event) {
        return $this->trigger(__FUNCTION__, $event);
    }

    protected function afterAction($event) {
        return $this->trigger(__FUNCTION__, $event);
    }
}
