<?php

namespace hikari\formatter;

class Text extends Formatter {
    public $contentType = 'text/plain';

    function run(&$result) {
        $result = gettype($result);
        return true;
    }
}