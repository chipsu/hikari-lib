<?php

namespace hikari\controller;

use \hikari\component\Component as Component;

abstract class ControllerAbstract extends Component implements ControllerInterface {
    public $action;
    public $view;
    public $id;

    function __construct(array $properties = []) {
        parent::__construct($properties);
        if(empty($this->id)) {
            $this->id = get_class($this);
        }
    }

    function run() {
        $methodName = $this->config->get('actionPrefix', '') . $this->action->id;
        $method = new \ReflectionMethod($this, $methodName);

        if(!$method->isPublic())
            \hikari\exception\Core::raise('Action "%s" is not public', $methodName);

        $args = []; // TODO: $action->request?
        $result = $method->invokeArgs($this, $args);

        if(is_array($result)) {
            $this->load('view', ['controller' => $this, 'data' => $result], ['register' => true]);
            return $this->view->render($this->action->id . '/' . $this->action->id);
        }
        return $result;
    }
}

