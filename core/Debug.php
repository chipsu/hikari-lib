<?php

namespace hikari\core;

class Debug {

    // TODO: Check content type
    function dump($var) {
        return $this->dumpHtml($var);
    }

    function dumpText($var) {
        return var_export($var, true);
    }

    function dumpHtml($var) {

    }
}
