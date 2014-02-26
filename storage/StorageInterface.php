<?php

namespace hikari\storage;

interface StorageInterface {
    function has($key);
    function get($key, $default = null);
    function set($key, $value, $options = null);
    function value($key, &$value);
    function values();
    function remove($key);
}
