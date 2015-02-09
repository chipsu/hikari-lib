<?php

namespace hikari\controller;

use \hikari\core\ComponentInterface;

interface ControllerInterface extends ComponentInterface {
    function run();
}
