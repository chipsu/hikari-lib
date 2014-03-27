<?php

namespace hikari\application;

use \hikari\autoload\Autoload as Autoload;
use \hikari\component\Component as Component;
use \hikari\config\Php as PhpConfig;

abstract class ApplicationAbstract extends Component implements ApplicationInterface {
    public $config = [];
    public $configHash;
    public $path;
    public $publicPath;
    public $runtimePath;

    function __construct(array $properties = array()) {
        $this->application = $this;
        if(empty($properties['path'])) {
            ArgumentException::raise('$properties[path]');
        }
        if(empty($properties['publicPath'])) {
            ArgumentException::raise('$properties[publicPath]');
        }
        $this->path = $properties['path'];
        unset($properties['path']);
        if(is_file($this->path . '/autoload.php')) {
            require($this->path . '/autoload.php');
        }
        if(empty($properties['config'])) {
            $configFile = empty($properties['configFile']) ? $this->path . '/config/main.php' : $properties['configFile'];
            if(is_file($configFile)) {
                $config = new PhpConfig;
                $config->load($configFile);
                $properties['config'] = $config;
            }
        }
        parent::__construct($properties);
        if(empty($this->runtimePath)) {
            $this->runtimePath = $this->path . '/runtime';
        }
    }
    
    public abstract function run();
}
