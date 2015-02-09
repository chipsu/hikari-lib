<?php

namespace hikari\core;

class Event extends Object {
    public $handled;
    public $result;
    public $headers; // Move to ActionEvent
    public $controller; // same
    public $action; // same

    public function __construct(array $properties) {
        foreach($properties as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
