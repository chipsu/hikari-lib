<?php

namespace hikari\router;

use \hikari\component\Component as Component;

abstract class RouterAbstract extends Component implements RouterInterface {

    function __construct(array $properties = []) {
        parent::__construct($properties);
    }

    function route($request) {

    }
}
