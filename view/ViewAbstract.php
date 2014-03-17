<?php

namespace hikari\view;

use \hikari\component\Component;

abstract class ViewAbstract extends Component implements ViewInterface {
    public $controller;
    public $data;
    public $layout = 'main';
    public $content;
    public $extension;
    public $paths = [];

    function __construct(array $parameters = []) {
        parent::__construct($parameters);
    }

    function initialize() {
        if(empty($this->paths)) {
            $this->paths[] = $this->application->path;
        }
    }

    function render($name) {
        $this->content = $this->partial('view/' . $name);
        return $this->partial('layout/' . $this->layout);
    }

    function partial($name, array $options = ['direct' => false]) {
        $file = $this->locate($name);
        $buffer = empty($options['direct']);
        if($buffer) {
            ob_start() or \hikari\exception\Core::raise('ob_start failed');
        }
        try {
            $this->includeFile($file);
        } catch(\Exception $ex) {
            if($buffer) ob_end_clean();
            throw $ex;
        }
        if($buffer) {
            return ob_get_clean();
        }
    }

    function locate($name) {
        foreach($this->paths as $path) {
            $file = $path . '/' . $name . '.' . $this->extension;
            if(is_file($file)) {
                return $file;
            }
        }
        \hikari\exception\NotFound::raise('Could not find view file "%s" in [%s]', $name, $this->paths);
    }

    function encode($string) {
        return htmlspecialchars($string);
    }

    function get($key, $default = null, $encode = true) {
        if(isset($this->data[$key])) {
            $result = $this->data[$key];
        } else {
            if(HI_LOG_W) \hiLog::w('Undefined view data key "%s"', $key);
            $result = $default;
        }
        return $encode ? htmlspecialchars($result) : $result;
    }

    function set($key, $value) {
        $this->data[$key] = $value;
        return $this;
    }

    function has($key) {
        return isset($this->data[$key]);
    }

    function str($key, array $args =  [], $encode = true) {
        if(isset($this->translator)) {
            return $encode ? htmlspecialchars($result) : $result;
        }
        return $key;
    }

    function url($route = null, array $args = []) {
        if(isset($this->router)) {
            return $this->router->build($route, $args);
        }
        return $route . '?' . http_build_query($args);
    }

    protected function includeFile(/* $file */) {
        extract($this->data);
        require(func_get_arg(0));
    }
}
