<?php

namespace hikari\view;

use \hikari\component\Component;

abstract class ViewAbstract extends Component implements ViewInterface {
    public $controller;
    public $cache;
    public $data;
    public $layout = 'main';
    public $content;
    public $extensions;
    public $paths = [];
    public $storage;
    public $compilers = [
        'htpl' => '\hikari\view\compiler\HtplCompiler',
        'haml' => '\hikari\view\compiler\HamlCompiler',
    ];
    public $executable = [
        'php', 'phtml',
    ];

    function __construct(array $parameters = []) {
        parent::__construct($parameters);
    }

    function initialize() {
        if(empty($this->paths)) {
            $this->paths[] = $this->application->path;
        }
        if(empty($this->extensions)) {

        }
        if(empty($this->storage)) {
            $this->storage = $this->application->runtimePath . '/views';
            is_dir($this->storage) or mkdir($this->storage, 0755, true);
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
        if(!$this->cache || !$this->cache->value([__FILE__, $name], $file)) {
            $file = $this->find($name);
            $type = pathinfo($file, PATHINFO_EXTENSION);
            if(!in_array($type, $this->executable)) {
                $compiler = new $this->compilers[$type];
                $result = $compiler->file($file);
                $file = $this->storage . '/' . sha1($file) . '.php';
                $compiler->store($file, $result);
            }
            if($this->cache) {
                $this->cache->set([__FILE__, $name], $file);
            }
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
