<?php

namespace hikari\controller;

use \hikari\core\Component;

abstract class ActionAbstract extends Component implements ActionInterface {
    public $id;
    public $result;

    function __construct(array $properties = []) {
        parent::__construct($properties);

        if(empty($this->id)) {
            \hikari\exception\Argument::raise('Action property "id" cannot be empty');
        }
    }

    function id() {
        return $this->id;
    }

    function result() {
        return $this->result;
    }
}

