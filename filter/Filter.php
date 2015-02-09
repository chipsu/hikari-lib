<?php

namespace hikari\filter;

use hikari\core\Mixin;

class Filter extends Mixin {
    function events() {
        return [
            'beforeAction' =>  [$this, 'beforeAction'],
            'afterAction' =>  [$this, 'afterAction'],
        ];
    }

    function beforeAction($event) {
    }

    function afterAction($event) {
    }
}
