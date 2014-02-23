<?php

namespace hikari\storage;

abstract class StorageAbstract implements StorageInterface {
    
    public function has($key) {
        \hikari\exception\NotSupported::raise();
    }

    public function get($key, $default = null) {
        \hikari\exception\NotSupported::raise();
    }

    public function set($key, $value, $options = null) {
        \hikari\exception\NotSupported::raise();
    }

    public function value($key, &$value) {
        \hikari\exception\NotSupported::raise();
    }

    public function values() {
        \hikari\exception\NotSupported::raise();
    }

    public function remove($key) {
        \hikari\exception\NotSupported::raise();
    }
}
