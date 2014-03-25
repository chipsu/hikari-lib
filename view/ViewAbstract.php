<?php

namespace hikari\view;

use \hikari\component\Component;

abstract class ViewAbstract extends Component implements ViewInterface {
    public $controller;
    public $data;
    public $layout = 'main';
    public $content;
    public $extensions;
    public $paths = [];

    function __construct(array $parameters = []) {
        parent::__construct($parameters);
    }

    function initialize() {
        if(empty($this->paths)) {
            $this->paths[] = $this->application->path;
        }
        parent::initialize();
    }

    function render($name) {
        $this->content = $this->view($name);
        return $this->layout($this->layout);
    }

    function view($name) {
        return $this->template('view/' . $name);
    }

    function layout($name) {
        return $this->template('layout/' . $name);
    }

    function template($name, array $options = ['direct' => false]) {
        $file = $this->find($name);
        if(strpos($file, '.htpl') !== false) {
            $htpl = new HtplCompiler;
            $json = $htpl->file($file);
            $jtpl = new JtplCompiler;
            $code = $jtpl->source($json);
            $temp = '/tmp/template-test.php';
            file_put_contents($temp, $code);
            $file = $temp;
        } else if(strpos($file, '.haml') !== false) {
            require_once $this->application->path . '/../lib/haml-php/src/HamlPHP/HamlPHP.php';
            require_once $this->application->path . '/../lib/haml-php/src/HamlPHP/Storage/FileStorage.php';
            \HamlPHP::$Config['escape_html_default'] = true;
            $parser = new \HamlPHP(new \FileStorage($this->application->path . '/runtime'));
            $content = $parser->parseFile($file);
            return $parser->evaluate($content, $this->data);
        }
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

    function find($name) {
        foreach($this->paths as $path) {
            foreach($this->extensions as $extension) {
                $file = $path . '/' . $name . '.' . $extension;
                if(is_file($file)) {
                    return $file;
                }
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
