<?php

namespace hikari\formatter;

abstract class Formatter extends \hikari\core\Component {
    abstract function run($event);
}