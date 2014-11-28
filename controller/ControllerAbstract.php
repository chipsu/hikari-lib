<?php

namespace hikari\controller;

use \hikari\component\Component as Component;

abstract class ControllerAbstract extends Component implements ControllerInterface {
    use ControllerTrait;

    function __construct(array $properties = []) {
        parent::__construct($properties);
        if(empty($this->id)) {
            $class = get_class($this);
            $class = explode('\\', $class);
            $this->id = strtolower(array_pop($class));
        }
    }
}
