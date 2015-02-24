<?php

namespace hikari\formatter;

class Json extends Formatter {
    public $contentType = 'application/json';
    public $options = \JSON_PRETTY_PRINT;

    function run($event) {
        $event->result = json_encode($event->result, $this->options);
        return true;
    }
}