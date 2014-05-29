<?php

namespace hikari\storage;

trait ArrayTrait {
    private $array;

    function bind(&$array) {
        $this->array = &$array;
    }

    function has($keys) {
        if(is_array($key)) {
            $data = $this->array;
            foreach($key as $k) {
                if(!isset($data[$ḱ])) {
                    return false;
                }
                $data = $data[$k];
            }
            return true;
        }
        return isset($this->array[$key]);
    }

    function get($key, $default = null) {
        if(is_array($key)) {
            $data = $this->array;
            foreach($key as $k) {
                if(!isset($data[$k])) {
                    return $default;
                }
                $data = $data[$k];
            }
            return $data;
        }
        return isset($this->array[$key]) ? $this->array[$key] : $default;
    }
    
    function set($key, $value, $options = null) {
        if(is_array($key)) {
            $data = &$this->array;
            $last = array_pop($key);
            foreach($key as $k) {
                $k = (string)$k;
                if(!isset($data[$k]) || !is_array($data[$k])) {
                    $data[$k] = [];
                }
                $data = &$data[$k];
            }
            $data[(string)$last] = $value;
        } else {
            $this->array[(string)$key] = $value;
        }
        return $this;
    }
    
    function value($key, &$value) {
        if(is_array($key)) {
            $data = $this->array;
            foreach($key as $k) {
                if(!isset($data[$k])) {
                    return false;
                }
                $data = $data[$k];
            }
            $value = $data;
            return true;
        } else if(isset($this->array[$key])) {
            $value = $this->array[$key];
            return true;
        }
        return false;
    }

    function values() {
        return $this->array;
    }
    
    function remove($key) {
        if(is_array($key)) {
            $data = &$this->array;
            $last = array_pop($key);
            foreach($key as $k) {
                if(!isset($data[$k])) {
                    return false;
                }
                $data = &$data[$k];
            }
            unset($data[$last]);
            return true;
        } else if(isset($this->array[$key])) {
            unset($this->array[$key]);
            return true;
        }
        return false;
    }
}
