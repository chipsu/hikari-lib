<?php

namespace hikari\view\htpl;

interface HtplInterface {
    function is_string($var);
    function is_traversable($var);
}

class Htpl implements HtplInterface {
    function is_string($var) {
        return is_string($var);
    }

    function is_traversable($var) {
        return is_array($var) || $var instanceof \Traversable;
    }

    function encode($var) {
        return htmlspecialchars($var);
    }
}
