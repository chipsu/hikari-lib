<?php

namespace hikari\core;

use Closure;

use \hikari\exception\InvalidOperation as InvalidOperationException;

class Object {

    static function createInstance($properties) {
        if($properties instanceof Closure) {
            $properties = $properties();
        } else if(is_string($properties)) {
            $properties = ['class' => $properties];
        }
        if(!isset($properties['class'])) {
            InvalidOperationException::raise('No class set, properties = %s', json_encode($properties));
        }
        $class = $properties['class'];
        unset($properties['class']);
        return new $class($properties);
    }

    function __construct(array $properties = []) {
        foreach($properties as $key => $value) {
            $this->$key = $value;
        }
    }

    function __set($name, $value) {
        $method = 'set' . $name;
        if(method_exists($this, $method)) {
            $this->$method($value);
            return;
        }
        InvalidOperationException::raise('Cannot set %s property %s::%s', method_exists($this, 'get' . $name) ? 'read-only' : 'unknown', get_class($this), $name);
    }

    function __isset($name) {
        $method = 'get' . $name;
        if(method_exists($this, $method)) {
            return $this->$method() !== null;
        }
        return false;
    }

    function __unset($name) {
        $method = 'set' . $name;
        if(method_exists($this, $method)) {
            $this->$method(null);
            return;
        }
        InvalidOperationException::raise('Cannot unset %s property %s::%s', method_exists($this, 'get' . $name) ? 'read-only' : 'unknown', get_class($this), $name);
    }
}
