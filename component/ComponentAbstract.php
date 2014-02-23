<?php

namespace hikari\component;

use \hikari\config\ConfigInterface as ConfigInterface;
use \hikari\config\Config as Config;

abstract class ComponentAbstract implements ComponentInterface {
    public $config = [];
    public $application;
    protected static $components = [];

    public function __construct(array $properties = []) {
        foreach($properties as $key => $value) {
            if(property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        if(!$this->config instanceof ConfigInterface) {
             $this->config = new Config($this->config);
        }
    }

    /**
     * Link a known component into this object.
     */
    public function component($component) {
        if(isset($this->$component)) {
            return $this->$component;
        }
        return $this->load($component, [], ['register' => true]);
    }

    /**
     * Link a Component into this object.
     */
    public function load($component, array $properties = [], array $options = []) {
        $result = null;

        // Grab config from application
        // TODO: rename to context? (this class shouldn't really be aware of the Application class)
        if(isset($this->application)) {
            $config =  $this->application->config->get(['component', $component], []);
            if(isset($config['options']))
                $options = array_merge($config['options'], $options);
            if(!isset($properties['application']))
                $properties['application'] = $this->application;
        }

        // Merge defaults
        $options = array_merge(['name' => false, 'register' => false, 'shared' => false], $options);
        $name = empty($options['name']) ? str_replace('\\', '_', $component) : $options['name'];

        // Find or create instance
        if($options['shared'] && isset(static::$components[$name])) {
            $result = static::$components[$name];
        } else {
            $class = isset($config['class']) ? $config['class'] : $component;
            if(isset($config['properties']))
                $properties = array_merge($config['properties'], $properties);
            if(!class_exists($class)) {
                // TODO: Component search paths/class prefixes
                $prefixes = ['\\app\\component\\', '\\hikari\\component\\'];
                foreach($prefixes as $prefix) {
                    if(class_exists($prefix . $class)) {
                        $class = $prefix . $class;
                        break;
                    }
                }
            }
            $result = new $class($properties);
            if($options['shared'])
                static::$components[$name] = $result;   
        }

        if($options['register']) {
            $name = empty($options['name']) ? str_replace('\\', '_', $component) : $options['name'];
            if(isset($this->$name))
                \hikari\exception\Core::raise('Property "%s" is already set', $name);
            $this->$name = $result;
        }

        return $result;
    }
}
