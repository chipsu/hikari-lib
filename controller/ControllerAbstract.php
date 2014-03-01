<?php

namespace hikari\controller;

use \hikari\component\Component as Component;

abstract class ControllerAbstract extends Component implements ControllerInterface {
    public $action;
    public $actions = [];
    public $request;
    public $view;
    public $id;

    function __construct(array $properties = []) {
        parent::__construct($properties);
        if(empty($this->id)) {
            $this->id = get_class($this);
        }
    }

    function run() {
        if($this->beforeAction()) {
            $methodName = isset($this->actions[$this->action->id])
                        ? $this->actions[$this->action->id]
                        : $this->config->get('actionPrefix', '') . $this->action->id;
            $method = new \ReflectionMethod($this, $methodName);
            $method->isPublic() ?: \hikari\exception\Core::raise('Action "%s" is not public', $methodName);

            $args = [];
            foreach($method->getParameters() as $param) {
                if(isset($this->request->get[$param->name])) {
                    $args[] = $this->request->get[$param->name];
                } else if(!$param->isOptional()) {
                    \hikari\exception\Argument::raise('Argument "%s" is missing for action "%s"', $param->name, $method->name);
                } else {
                    $args[] = $param->getDefaultValue();
                }
            }
            $this->action->result = $method->invokeArgs($this, $args);
            $this->afterAction();

            if(is_array($this->action->result)) {
                $this->load('view', ['controller' => $this, 'data' => $this->action->result], ['register' => true]);
                return $this->view->render($this->action->id . '/' . $this->action->id);
            }
            return $this->action->result;
        }
        return false;
    }

    protected function beforeAction() {
        return true;
    }

    protected function afterAction() {
        return true;
    }
}
