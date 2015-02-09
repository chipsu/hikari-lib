<?php

namespace hikari\controller;

use \hikari\core\Component;
use \hikari\core\Event;

abstract class ControllerAbstract extends Component implements ControllerInterface {
    public $action;
    public $actions = [];
    public $request;
    public $view;
    public $viewFile;
    public $id;

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

    protected function viewFile() {
        if($this->viewFile == null) {
            return $this->id . '/' . $this->action->id;
        }
        return $this->viewFile;
    }

    protected function beforeAction($event) {
        return $this->trigger(__FUNCTION__, $event);
    }

    protected function afterAction($event) {
        return $this->trigger(__FUNCTION__, $event);
    }
}
