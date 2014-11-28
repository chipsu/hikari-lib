<?php

namespace hikari\controller;

trait ControllerTrait {
    public $action;
    public $actions = [];
    public $request;
    public $view;
    public $viewFile;
    public $id;

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
                if($this->beforeRender()) {
                    $viewFile = $this->viewFile();
                    $result = $this->view->render($viewFile);
                    $this->afterRender();
                    return $result;
                }
            }
            return $this->action->result;
        }
        return false;
    }

    protected function viewFile() {
        if($this->viewFile == null) {
            return $this->id . '/' . $this->action->id;
        }
        return $this->viewFile;
    }

    protected function beforeAction() {
        return true;
    }

    protected function afterAction() {
    }

    protected function beforeRender() {
        return true;
    }

    protected function afterRender() {
    }
}
