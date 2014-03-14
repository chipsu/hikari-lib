<?php

namespace hikari\storage;

trait StorageTrait {
    function has($key) {
        \hikari\exception\NotSupported::raise();
    }

    function get($key, $default = null) {
        \hikari\exception\NotSupported::raise();
    }

    function set($key, $value, $options = null) {
        \hikari\exception\NotSupported::raise();
    }

    function value($key, &$value) {
        \hikari\exception\NotSupported::raise();
    }

    function values() {
        \hikari\exception\NotSupported::raise();
    }

    function remove($key) {
        \hikari\exception\NotSupported::raise();
    } 
}
