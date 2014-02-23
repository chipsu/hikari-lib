<?php

namespace hikari\router;

use \hikari\component\Component as Component;

abstract class RouterAbstract extends Component implements RouterInterface {

    public function __construct(array $properties = []) {
        parent::__construct($properties);
    }

    public function route($request) {

    }
}
