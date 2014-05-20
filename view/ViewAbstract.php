<?php

namespace hikari\view;

use \hikari\component\Component;

abstract class ViewAbstract extends Component implements ViewInterface {
    public $controller;
    public $cache;
    public $watch = true;
    public $data;
    public $layout = 'main';
    public $content;
    public $extensions;
    public $paths = [];
    public $storage;
    public $compilers = [
        'htpl' => '\hikari\view\compiler\HtplCompiler',
        'haml' => '\hikari\view\compiler\HamlCompiler',
        'ptpl' => '\hikari\view\compiler\PtplCompiler',
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

    function view($name, array $options = []) {
        return $this->template('view/' . $name, $options);
    }

    function layout($name, array $options = []) {
        return $this->template('layout/' . $name, $options);
    }

    function template($name, array $options = []) {
        $cacheKey = $this->cache ? [__FILE__, $name, json_encode($options)] : false;
        if(!$this->cache || !$this->cache->value($cacheKey, $file)) {
            $source = $this->find($name);
            $type = pathinfo($source, PATHINFO_EXTENSION);
            if(!in_array($type, $this->executable)) {
                $compiler = new $this->compilers[$type];
                $output = isset($options['output']) ? $options['output'] : 'php';
                $result = $compiler->file($source, ['output' => $output]);
                $file = $this->storage . '/' . sha1($source) . '.' . $output;
                $compiler->store($file, $result);
            } else {
                $file = $source;
            }
            if($this->cache) {
                $this->cache->set($cacheKey, $file, !$this->watch ?: [
                    'watch' => ['src' => $source, 'dst' => $file]
                ]);
            }
        }
        if(!empty($options['source'])) {
            return file_get_contents($file);
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

    function read($name) {
        $file = $this->find($name);
        return file_get_contents($file);
    }

    function encode($string) {
        return htmlspecialchars($string);
    }

    function get($key, $default = null, $encode = true) {
        if(isset($this->data[$key])) {
            $result = $this->data[$key];
        } else {
            #if(HI_LOG_W) \hiLog::w('Undefined view data key "%s"', $key);
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

    function url($route = null, array $args = [], $auto = false) {
        if(isset($this->router)) {
            if($auto) {
                if(isset($this->controller) && !isset($args['class'])) {
                    $args['class'] = explode('\\', get_class($this->controller));
                    $args['class'] = lcfirst(array_pop($args['class']));
                }
                if(isset($this->controller->action) && !isset($args['action'])) {
                    $args['action'] = $this->controller->action->id;
                }
            }
            return $this->router->build($route, $args);
        }
        return $route . '?' . http_build_query($args);
    }

    protected function includeFile(/* $file */) {
        extract($this->data);
        require(func_get_arg(0));
    }
}
