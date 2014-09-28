<?php

namespace hikari\controller;

abstract class ActionAbstract extends \hikari\component\Component implements ActionInterface {
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
}

