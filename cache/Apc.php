<?php

namespace hikari\cache;

class Apc extends CacheAbstract {
    protected $config;
    protected $ttl;

    function __construct($config = null) {
        if($config) {
            $this->ttl = $config->get(['cache', 'apc', 'ttl'], 0);
        }
    }

    function has($key) {
        if(is_array($key)) $key = implode(PHP_EOL, $key);
        return apc_exists($key);
    }

    function get($key, $default = null) {
        if(is_array($key)) $key = implode(PHP_EOL, $key);
        $result = apc_fetch($key, $success);
        return $success ? $result : $default;
    }

    function set($key, $value, $options = null) {
        if(is_array($key)) $key = implode(PHP_EOL, $key);
        $ttl = isset($options['ttl']) ? $options['ttl'] : $this->ttl;
        $result = apc_store($key, $value, $ttl);
        return $this;
    }

    function value($key, &$value) {
        if(is_array($key)) $key = implode(PHP_EOL, $key);
        $value = apc_fetch($key, $success);
        return $success;
    }

    function values() {
        \hikari\exception\NotSupported::raise();
    }

    function remove($key) {
        if(is_array($key)) $key = implode(PHP_EOL, $key);
        apc_delete($key);
        return $this;
    }
}
