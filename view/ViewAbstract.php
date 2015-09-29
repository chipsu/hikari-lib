<?php

namespace hikari\view;

use \hikari\core\Component;

# TODO: Move helpers away
# use something else, like a "magic" data variable, like
# $_->helper() or just export helpers like: $_helper() (internal renderer only)
abstract class ViewAbstract extends Component implements ViewInterface {
    public $controller;
    public $cache;
    public $watch = true;
    public $data = [];
    public $layout = 'main';
    public $content;
    private $_paths;
    private $_router;
    public $extensions = [
        'php' => 'php',
        'phtml' => 'php',
        'twig' => 'twig',
        'htpl' => 'htpl',
    ];
    public $renderers = [
        'php' => '\hikari\view\renderer\Php',
        'htpl' => '\hikari\view\renderer\Htpl',
        'twig' => '\hikari\view\renderer\Twig',
    ];

    function getPaths() {
        if($this->_paths === null) {
            $this->paths = [$this->application->path];
        }
        return $this->_paths;
    }

    function setPaths(array $paths) {
        $this->_paths = $this->application->expandArray($paths);
    }

    // TODO: Not sure about this
    function getRouter() {
        if($this->_router === null) {
            if($this->controller) {
                $this->_router = $this->controller->getRouter();
            }
            if($this->_router === null) {
                $this->_router = $this->createComponent('router', [/*'context' => $this->getContext()*/]);
            }
        }
        return $this->_router;
    }

    function setRouter($value) {
        $this->_router = $value;
    }

    function getRenderer($name) {
        if(!isset($this->renderers[$name])) {
            \hikari\exception\Argument::raise('Renderer "%s" not found');
        }
        if(!is_object($this->renderers[$name])) {
            $this->renderers[$name] = $this->createComponent($this->renderers[$name]);
        }
        return $this->renderers[$name];
    }

    function getRendererFromExtension($extension) {
        if(!isset($this->extensions[$extension])) {
            \hikari\exception\Argument::raise('Alias "%s" not found');
        }
        $name = $this->extensions[$extension];
        return $this->getRenderer($name);
    }

    function document($name, array $data = [], array $options = []) {
        $this->set('view', $this); // TODO: Use context
        $this->set('content', $this->view($name)); // TODO: Use a callback instead
        return $this->layout($this->layout, $data, $options);
    }

    function view($name, array $data = [], array $options = []) {
        return $this->render('view/' . $name, $data, $options);
    }

    function layout($name, array $data = [], array $options = []) {
        return $this->render('layout/' . $name, $data, $options);
    }

    function render($name, array $data = [], array $options = []) {
        $source = $this->find($name);
        $extension = pathinfo($source, PATHINFO_EXTENSION);
        $renderer = $this->getRendererFromExtension($extension);
        $options = array_merge(['context' => $this], $options); // TODO: Context should be a separate class
        $data = array_merge($this->data, $data);
        return $renderer->render($source, $data, $options);
    }

    function find($name) {
        $cacheKey = [__FILE__, $name];
        if($this->cache && $this->cache->value($cacheKey, $file)) {
            return $file;
        }
        foreach($this->paths as $path) {
            foreach(array_keys($this->extensions) as $extension) {
                $file = $path . '/' . $name . '.' . $extension;
                if(is_file($file)) {
                    if($this->cache) {
                        $this->cache->set($cacheKey, $file);
                    }
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

    //////// Move below to helpers

    function str($key, array $args =  [], $encode = true) {
        if(isset($this->translator)) {
            return $encode ? htmlspecialchars($result) : $result;
        }
        return $key;
    }

    function encode($string) {
        return htmlspecialchars($string);
    }

    function url($route = null, array $args = [], $auto = false) {
        if($router = $this->getRouter()) {
            if($auto) {
                if(isset($this->controller) && !isset($args['class'])) {
                    $args['class'] = explode('\\', get_class($this->controller));
                    $args['class'] = lcfirst(array_pop($args['class']));
                }
                if(isset($this->controller->action) && !isset($args['action'])) {
                    $args['action'] = $this->controller->action->id;
                }
            }
            return (string)$router->build($route, $args);
        }
        return $route . '?' . http_build_query($args);
    }
}
