<?php

namespace hikari\view\renderer;

use hikari\core\Component;

class Cache extends \Twig_Cache_Filesystem {

    public function load($key) {
        if(is_file($key)) {
            include_once $key;
        }
    }
}

class Loader implements \Twig_LoaderInterface {

    public function getSource($name) {
        return file_get_contents($name);
    }

    public function exists($name) {
        return is_file($name);
    }

    public function getCacheKey($name) {
        return sha1($name);
    }

    public function isFresh($name, $time) {
        return true;
    }
}

class Twig extends Component implements RendererInterface {
    private $_twig;

    function getTwig() {
        if($this->_twig == null) {
            $loader = new Loader;
            $this->_twig = new \Twig_Environment($loader);
            $cachePath = $this->application->expand('@runtime/twig-cache', ['mkdir' => true]);
            $this->_twig->setCache(new Cache($cachePath));
        }
        return $this->_twig;
    }

    function render($source, array $data = [], array $options = []) {
        return $this->getTwig()->render($source, $data);
    }
}
