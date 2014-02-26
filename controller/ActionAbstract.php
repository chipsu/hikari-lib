<?php

namespace hikari\controller;

abstract class ActionAbstract extends \hikari\component\Component implements ActionInterface {
    public $id;

    function id() {
        return $this->id;
    }
}

