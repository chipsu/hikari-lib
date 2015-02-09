<?php

namespace hikari\core;

class Mixin extends Object implements MixinInterface {
    private $_context;

    function events() {
        return [];
    }

    function attach($context) {
        $this->detach();
        if($context) {
            $this->_context = $context;
            foreach($this->events() as $name => $event) {
                $this->_context->on($name, $event);
            }
        }
    }

    function detach() {
        if($this->_context) {
            foreach($this->events() as $name => $event) {
                $this->_context->off($name, $event);
            }
            $this->_context = null;
        }
    }
}
