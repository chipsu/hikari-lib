<?php

namespace hikari\storage;

interface StorageInterface {
    public function has($key);
    public function get($key, $default = null);
    public function set($key, $value, $options = null);
    public function value($key, &$value);
    public function values();
    public function remove($key);
}
