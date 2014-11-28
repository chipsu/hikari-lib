<?php

namespace hikari\controller;

trait RestTrait {
    use ModelTrait;

    function get() {
        \hikari\exception\NotSupported::raise();
    }

    function put() {
        \hikari\exception\NotSupported::raise();
    }

    function post() {
        \hikari\exception\NotSupported::raise();
    }

    function delete() {
        \hikari\exception\NotSupported::raise();
    }
}
