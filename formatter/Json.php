<?php

namespace hikari\formatter;

class Json extends Formatter {
    public $contentType = 'application/json';
    public $options = \JSON_PRETTY_PRINT;

    function run(&$result) {
        $result = json_encode($result, $this->options);
        return true;
    }
}