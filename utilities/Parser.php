<?php

namespace hikari\utilities;

use \hikari\exception\Core as CoreException;

class Parser {
    public $rules = [];

    function run(array $tokens) {
        if(empty($this->rules)) {
            CoreException::raise('No rules set');
        }
        \hikari\exception\NotImplemented::raise();
    }

    function match(array $token) {
        \hikari\exception\NotImplemented::raise();
        foreach($this->rules as $rule) {
            #if(...) {
            #    $this->emit($rule['...']);
            #}
        }
    }
}
