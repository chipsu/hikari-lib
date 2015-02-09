<?php

namespace hikari\controller;

use \hikari\core\ComponentInterface;

interface ActionInterface extends ComponentInterface {
    function id();
    function result();
    function invoke(array $args);
}

