<?php

namespace hikari\controller;

class Action extends ActionAbstract {
    public $method;
    public $context;

    function invoke(array $args = []) {
        $this->method->isPublic() ?: \hikari\exception\Core::raise('Action "%s" is not public', $this->methodName);
        foreach($this->method->getParameters() as $param) {
            if(isset($this->request->get[$param->name])) {
                $args[] = $this->request->get[$param->name];
            } else if(!$param->isOptional()) {
                \hikari\exception\Argument::raise('Argument "%s" is missing for action "%s"', $param->name, $this->method->name);
            } else {
                $args[] = $param->getDefaultValue();
            }
        }
        return $this->method instanceof \ReflectionMethod
             ? $this->method->invokeArgs($this->context, $args)
             : $this->method->invokeArgs($args);
    }
}
