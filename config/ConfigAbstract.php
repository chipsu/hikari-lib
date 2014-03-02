<?php

namespace hikari\config;

abstract class ConfigAbstract extends \hikari\storage\ArrayAbstract implements ConfigInterface {
    public $data;
    public $hash;

    function __construct($data = []) {
        $this->hash = __FILE__;
        $this->load($data);
        parent::__construct($this->data);
    }

    function load($config) {
        if($config instanceof \hikari\storage\StorageInterface) {
            $this->data = $config->values();
        } else if(is_array($config)) {
            $this->data = $config;
        } else {
            \hikari\exception\Argument::raise('Unsupported config type "%s"', gettype($config));
        }
        return $this;
    }

    function merge($config, $overwrite = false) {
        if($config instanceof \hikari\storage\StorageInterface) {
            $this->data = $overwrite ? \hikari\utilities\Arrays::merge($this->data, $config->values()) : \hikari\utilities\Arrays::merge($config->values(), $this->data);
        } else if(is_array($config)) {
            $this->data = $overwrite ? \hikari\utilities\Arrays::merge($this->data, $config) : \hikari\utilities\Arrays::merge($config, $this->data);
        } else {
            \hikari\exception\Argument::raise('Unsupported config type "%s"', gettype($config));
        }
        return $this;
    }
}
