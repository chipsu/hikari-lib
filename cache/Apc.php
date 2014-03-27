<?php

namespace hikari\cache;

class Apc extends CacheAbstract {
    public $ttl;

    function __construct(array $parameters = []) {
        parent::__construct($parameters);
        $this->ttl = $this->config->get(['cache', 'apc', 'ttl'], 0);
    }

    function has($key) {
        if(is_array($key)) $key = implode(PHP_EOL, $key);
        return apc_exists($key);
    }

    function get($key, $default = null) {
        if(is_array($key)) $key = implode(PHP_EOL, $key);
        $result = apc_fetch($key, $success);
        return $success && $this->validate($result) ? $result['value'] : $default;
    }

    function set($key, $value, $options = null) {
        if(is_array($key)) $key = implode(PHP_EOL, $key);
        $ttl = isset($options['ttl']) ? $options['ttl'] : $this->ttl;
        $result = apc_store($key, ['value' => $value, 'options' => $options], $ttl);
        return $this;
    }

    function value($key, &$value) {
        if(is_array($key)) $key = implode(PHP_EOL, $key);
        $result = apc_fetch($key, $success);
        $value = isset($result['value']) ? $result['value'] : null;
        return $success && $this->validate($result);
    }

    function values() {
        \hikari\exception\NotSupported::raise();
    }

    function remove($key) {
        if(is_array($key)) $key = implode(PHP_EOL, $key);
        apc_delete($key);
        return $this;
    }

    function validate($result) {
        if(isset($result['options']['watch'])) {
            $watch = $result['options']['watch'];
            $src = $watch['src'];
            $dst = $watch['dst'];
            if(!is_file($src) || !is_file($dst) || filemtime($src) > filemtime($dst)) {
                return false;
            }
        }
        return isset($result['value']);
    }
}
