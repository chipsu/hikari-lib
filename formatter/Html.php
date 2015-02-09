<?php

namespace hikari\formatter;

class Html extends Formatter {
    public $contentType = 'text/html';

    function run(&$result) {
        $result = var_dump($result, true);
        return true;
    }
}