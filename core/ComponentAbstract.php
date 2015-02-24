<?php

namespace hikari\core;

use \hikari\config\ConfigInterface as ConfigInterface;
use \hikari\config\Config as Config;
use \hikari\exception\InvalidOperation as InvalidOperationException;

abstract class ComponentAbstract extends Object implements ComponentInterface {
    public $class;
    public $shared;
    public $register;
    private $_components;
    private $__components = [];
    private $_mixins;
    private $__mixins = [];
    private $_application;
    private $_config;
    protected static $_sharedComponents = [];
    private $_events = [];

    function __construct(array $properties = []) {
        foreach(['components', 'mixins'] as $key) {
            if(isset($properties[$key])) {
                $this->{'__' . $key} = $properties[$key];
                unset($properties[$key]);
            }
        }
        parent::__construct($properties);
        $this->init();
    }

    function init() {
        $this->getComponents();
        $this->getMixins();
    }

    function __set($name, $value) {
        $method = 'set' . $name;
        if(method_exists($this, $method)) {
            $this->$method($value);
            return;
        }
        foreach($this->getMixins() as $mixin) {
            if(method_exists($mixin, $method)) {
                $mixin->$method($value);
                return;
            } else if(property_exists($mixin, $name)) {
                $mixin->$name = $value;
                return;
            }
        }
        InvalidOperationException::raise('Cannot set %s property %s::%s', method_exists($this, 'get' . $name) ? 'read-only' : 'unknown', get_class($this), $name);
    }

    function __get($name) {
        $method = 'get' . $name;
        if(method_exists($this, $method)) {
            return $this->$method();
        }
        if(isset($this->_components[$name])) {
            return $this->_components[$name];
        }
        foreach($this->getMixins() as $mixin) {
            if(method_exists($mixin, $method)) {
                return $mixin->$method($value);
            } else if(property_exists($mixin, $name)) {
                return $mixin->$name;
            }
        }
        InvalidOperationException::raise('Cannot get %s property %s::%s', method_exists($this, 'set' . $name) ? 'write-only' : 'unknown', get_class($this), $name);
    }

    function __isset($name) {
        $method = 'get' . $name;
        if(method_exists($this, $method)) {
            return $this->$method() !== null;
        }
        foreach($this->getMixins() as $mixin) {
            if(method_exists($mixin, $method)) {
                return $mixin->$method($value) !== null;
            } else if(property_exists($mixin, $name)) {
                return $mixin->$name !== null;
            }
        }
        return false;
    }

    function __unset($name) {
        $method = 'set' . $name;
        if(method_exists($this, $method)) {
            $this->$method(null);
            return;
        }
        foreach($this->getMixins() as $mixin) {
            if(method_exists($mixin, $method)) {
                $mixin->$method(null);
                return;
            } else if(property_exists($mixin, $name)) {
                $mixin->$name = null;
                return;
            }
        }
        InvalidOperationException::raise('Cannot unset %s property %s::%s', method_exists($this, 'get' . $name) ? 'read-only' : 'unknown', get_class($this), $name);
    }

    function components() {
        return $this->__components;
    }

    function getComponents() {
        if($this->_components === null) {
            $this->_components = [];
            foreach($this->components() as $name => $component) {
                if($component instanceof ComponentInterface) {
                    $this->_components[$name] = $component;
                } else {
                    if(is_string($component)) {
                        $name = $component;
                        $component = [];
                    }
                    $this->_components[$name] = $this->createComponent($name, $component);
                }
            }
        }
        return $this->_components;
    }

    function mixins() {
        return $this->__mixins;
    }

    function getMixins() {
        if($this->_mixins === null) {
            $this->_mixins = [];
            foreach($this->mixins() as $name => $mixin) {
                $this->attachMixin($name, $mixin);
            }
        }
        return $this->_mixins;
    }

    function attachMixin($name, $mixin) {
        if(!$mixin instanceof MixinInterface) {
            $mixin = Object::createInstance($mixin);
            if(!$name !== null && !is_numeric($name)) {
                if(isset($this->_mixins[$name])) {
                    $this->removeMixin($name);
                }
                $mixin->attach($this);
                $this->_mixins[$name] = $mixin;
            } else {
                $this->_mixins[] = $mixin;
            }
        }
        return $mixin;
    }

    function createComponent($name, array $properties = []) {
        $properties = array_merge(['shared' => false, 'register' => true], $this->getConfig()->get(['component', $name], []), $properties);
        if($properties['shared'] && isset(static::$_sharedComponents[$name])) {
            $instance = static::$_sharedComponents[$name];
        } else {
            if(!isset($properties['class'])) {
                $properties['class'] = $name;
            }
            $instance = Object::createInstance($properties);
            if($properties['shared']) {
                static::$_sharedComponents[$name] = $instance;
            }
        }
        /*if($properties['register']) {
            if(isset($this->$name)) {
                InvalidOperationException::raise('A component with the name %s already exists', $name);
            }
            $this->$name = $instance;
        }*/
        $this->_components[$name] = $instance;
        return $instance;
    }

    function detachMixin($name) {
        $this->getMixins();
        if(isset($this->_mixins[$name])) {
            $mixin = $this->_mixins[$name];
            unset($this->_mixins[$name]);
            $mixin->detach();
            return $mixin;
        }
        return null;
    }

    function on($name, $callback) {
        if(!isset($this->_events[$name])) {
            $this->_events[$name] =  [];
        }
        $this->_events[$name][] = $callback;
        return $this;
    }

    function off($name, $callback) {
        if(isset($this->_events[$name])) {
            foreach($this->_events[$name] as $key => $value) {
                if($value === $callback) {
                    unset($this->_events[$name][$key]);
                    break;
                }
            }
        }
        return $this;
    }

    function trigger($name, $event) {
        if(isset($this->_events[$name])) {
            foreach($this->_events[$name] as $key => $value) {
                if(call_user_func($value, $event) === false || $event->handled) {
                    break;
                }
            }
        }
        return $this;
    }

    function getConfig() {
        if($this->_config === null) {
            if($application = $this->application) {
                $this->_config = $application->config;
            } else {
                $this->_config = new Config;
            }
        }
        return $this->_config;
    }

    function getApplication() {
        if($this->_application === null) {
            $this->_application = Application::$instance;
        }
        return $this->_application;
    }

    private function setApplication($value) {
        $this->_application = $value;
    }

    /**
     * Link a known component into this object.
     */
    function xxx_component($component) {
        if(isset($this->$component)) {
            return $this->$component;
        }
        return $this->load($component, [], ['register' => true]);
    }

    /**
     * Link a Component into this object.
     */
    function xxx_load($component, array $properties = [], array $options = []) {
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

        // Merge with local options
        if(isset($this->componentOptions[$component]))
            $options = array_merge($options, $this->componentOptions[$component]);

        // Merge defaults
        $options = array_merge(['name' => false, 'register' => false, 'shared' => false], $options);
        $name = empty($options['name']) ? str_replace('\\', '_', $component) : $options['name'];

        // Find or create instance
        if($options['shared'] && isset(static::$components[$name])) {
            $result = static::$components[$name];
        } else {
            $class = isset($config['class']) ? $config['class'] : $component;
            // Merge with local properties
            if(isset($this->componentProperties[$component]))
                $properties = array_merge($properties, $this->componentProperties[$component]);
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
                if(!class_exists($class)) {
                    \hikari\exception\Core::raise('class "%s" not found', $class);
                }
            }
            $result = new $class($properties);
            if($options['shared']) {
                static::$components[$name] = $result;
            }
            if(isset($options['components'])) {
                foreach($options['components'] as $name) {
                    $result->component($name);
                }
            }
        }

        if($options['register']) {
            $name = empty($options['name']) ? str_replace('\\', '_', $component) : $options['name'];
            if(isset($this->$name) && empty($options['replace']))
                \hikari\exception\Core::raise('Property "%s" is already set', $name);
            $this->$name = $result;
        }

        return $result;
    }
}
