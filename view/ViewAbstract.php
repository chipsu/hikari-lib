<?php

namespace hikari\view;

use \hikari\component\Component;

abstract class ViewAbstract extends Component implements ViewInterface {
    public $controller;
    public $data;
    public $layout = 'main';
    public $content;
    public $extension;
    public $translator;

    public function translator() {
        return $this->component('translator');
    }

    public function render($name) {
        $this->content = $this->partial('view/' . $name);
        return $this->partial('layout/' . $this->layout);
    }

    public function partial($name, array $options = ['direct' => false]) {
        // FIXME: hardcoded path
        $file = $this->application->path . '/' . $name . '.' . $this->extension;
        if(!is_file($file)) {
            \hikari\exception\NotFound::raise('Could not find view file "%s"', $file);
        }
        $buffer = empty($options['direct']);
        if($buffer) {
            if(!ob_start())
                \hikari\exception\Core::raise('ob_start failed');
        }
        $this->includeFile($file);
        if($buffer) {
            return ob_get_clean();
        }
    }

    public function encode($string) {
        return htmlspecialchars($string);
    }

    public function get($key, $default = null, $encode = true) {
        if(isset($this->data[$key])) {
            $result = $this->data[$key];
        } else {
            if(HI_LOG_W) \hiLog::w('Undefined view data key "%s"', $key);
            $result = $default;
        }
        return $encode ? htmlspecialchars($result) : $result;
    }

    public function set($key, $value) {
        $this->data[$key] = $value;
        return $this;
    }

    public function has($key) {
        return isset($this->data[$key]);
    }

    public function str($key, array $args =  [], $encode = true) {
        $result = $this->translator()->translate($key);
        return $encode ? htmlspecialchars($result) : $result;        
    }

    protected function includeFile($file) {
        extract($this->data);
        require(func_get_arg(0));
    }
}
