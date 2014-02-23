<?php

namespace hikari\exception;

class Exception extends \Exception {

    static public function raise($inner = null, $code = 0, $format = null) {
        $args = func_get_args();
        $class = get_called_class();
        $previous = !empty($args) && $args[0] instanceof \Exception ? array_shift($args) : null;
        $code = !empty($args) && is_numeric($args[0]) ? array_shift($args) : 0;
        if(empty($args)) {
            $message = $class;
        } else {
            $format = array_shift($args);
            $message = vsprintf($format, $args);
        }
        throw new $class($message, $code, $previous);
    }
    
    public function __construct($message, $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}