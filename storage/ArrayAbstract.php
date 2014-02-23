<?php

namespace hikari\storage;

abstract class ArrayAbstract extends StorageAbstract {
    private $data;

    protected function __construct(&$data) {
        $this->data = &$data;
    }

    public function has($keys) {
        if(is_array($key)) {
            $data = $this->data;
            foreach($key as $k) {
                if(!isset($data[$ḱ])) {
                    return false;
                }
                $data = $data[$k];
            }
            return true;
        }
        return isset($this->data[$key]);
    }

    public function get($key, $default = null) {
        if(is_array($key)) {
            $data = $this->data;
            foreach($key as $k) {
                if(!isset($data[$k])) {
                    return $default;
                }
                $data = $data[$k];
            }
            return $data;
        }
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
    
    public function set($key, $value, $options = null) {
        if(is_array($key)) {
            $data = &$this->data;
            $last = array_pop($key);
            foreach($key as $k) {
                if(!isset($data[$k]) || !is_array($data[$k])) {
                    $data[$k] = [];
                }
                $data = &$data[$k];
            }
            $data[$last] = $value;
        } else {
            $this->data[$key] = $value;
        }
        return $this;
    }
    
    public function value($key, &$value) {
        if(is_array($key)) {
            $data = $this->data;
            foreach($key as $k) {
                if(!isset($data[$k])) {
                    return false;
                }
                $data = $data[$k];
            }
            $value = $data;
            return true;
        } else if(isset($this->data[$key])) {
            $value = $this->data[$key];
            return true;
        }
        return false;
    }

    public function values() {
        return $this->data;
    }
    
    public function remove($key) {
        if(is_array($key)) {
            $data = &$this->data;
            $last = array_pop($key);
            foreach($key as $k) {
                if(!isset($data[$k])) {
                    return false;
                }
                $data = &$data[$k];
            }
            unset($data[$last]);
            return true;
        } else if(isset($this->data[$key])) {
            unset($this->data[$key]);
            return true;
        }
        return false;
    }
}
