<?php

namespace hikari\config;

class Php extends Config {
    public $cache;

    function __construct(array $data = []) {
        parent::__construct($data);
    }

    function load($config) {
        if(is_string($config)) {
            is_file($config) or \hikari\exception\Argument::raise('File "%s" does not exist!', $config);
            $this->hash = filemtime($config);
            if(!$this->cache || !$this->cache->value([$this->hash, $config], $this->data)) {
                $this->data = [];
                $this->merge($config, true);
                if($file = \hikari\utilities\File::getUserFile($config, true)) {
                    $this->merge($file, true);
                }
                if($this->cache) {
                    $this->cache->set([$this->hash, $config], $this->data);
                }
            }
            return $this;
        }
        return parent::load($config);
    }

    function merge($config, $overwrite = false) {
        if(is_string($config)) {
            is_file($config) or \hikari\exception\Argument::raise('File "%s" does not exist!', $config);
            if($result = require($config)) {
                $this->data = $overwrite ? \hikari\utilities\Arrays::merge($this->data, $result) : \hikari\utilities\Arrays::merge($result, $this->data);
            }
            return $this;
        }
        return parent::merge($config, $overwrite);
    }
    
    function save($filename) {
        $data = '<?php '.PHP_EOL;
        $data .= '$config = ';
        $data .= $this->toPhpConfig();
        if(false === @file_put_contents($filename, $data, LOCK_EX)) {
            \hikari\exception\Argument::raise('Could not write config to "%s"!', $filename);
        }
    }
    
    function toPhpConfig() {
        $result = '';
        foreach($this->data as $key => $value) {
            $result .= '$this->set(\''.addslashes($key).'\', ';
            $result .= self::toPhpValue($value);
            $result .= ', true);'.PHP_EOL;
        }
        return $result;
    }

    static protected function toPhpValue($value, $level = 0) {
        if(is_array($value)) {
            $result = 'array('.PHP_EOL;
            foreach($value as $k => $v) {
                $result .= str_repeat('    ', $level + 1);
                if(is_numeric($k)) {
                    $result .= $k.' => ';
                } else {
                    $result .= '\''.addslashes($k).'\' => ';
                }
                $result .= self::toPhpValue($v, $level + 1);
                $result .= ','.PHP_EOL;
            }
            $result .= str_repeat('    ', $level).')';
        } else {
            $result = '\''.(string)$value.'\'';
        }
        return $result;
    }
}
