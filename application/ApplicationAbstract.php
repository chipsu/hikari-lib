<?php

namespace hikari\application;

use \hikari\autoload\Autoload as Autoload;
use \hikari\component\Component as Component;
use \hikari\config\Php as PhpConfig;

abstract class ApplicationAbstract extends Component implements ApplicationInterface {
    public $config = [];
    public $path;
    public $publicPath;

    public function __construct(array $properties = array()) {
        $this->application = $this;
        if(empty($properties['path'])) {
            ArgumentException::raise('$properties[path]');
        }
        if(empty($properties['publicPath'])) {
            ArgumentException::raise('$properties[publicPath]');
        }
        $this->path = $properties['path'];
        unset($properties['path']);
        Autoload::push($this->path . '/..');
        if(empty($properties['config'])) {
            $configFile = empty($properties['configFile']) ? $this->path . '/config/main.php' : $properties['configFile'];
            if(is_file($configFile)) {
                $config = new PhpConfig;
                $config->load($configFile);
                $properties['config'] = $config;
            }
        }
        parent::__construct($properties);
    }
    
    public abstract function run();
}
