<?php

namespace hikari\formatter;

class Text extends Formatter {
    public $contentType = 'text/plain';

    function run($event) {
        $event->result = gettype($event->result);
        return true;
    }
}