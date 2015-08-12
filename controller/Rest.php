<?php

namespace hikari\controller;

class Rest extends Model implements RestInterface {

    function head() {
        \hikari\exception\NotImplemented::raise(__METHOD__);
    }

    function get() {
        \hikari\exception\NotImplemented::raise(__METHOD__);
    }

    function put() {
        \hikari\exception\NotImplemented::raise(__METHOD__);
    }

    function post() {
        \hikari\exception\NotImplemented::raise(__METHOD__);
    }

    function patch() {
        \hikari\exception\NotImplemented::raise(__METHOD__);
    }

    function delete() {
        \hikari\exception\NotImplemented::raise(__METHOD__);
    }

    function options() {
        \hikari\exception\NotImplemented::raise(__METHOD__);
    }

}
