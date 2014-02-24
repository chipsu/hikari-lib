<?php

namespace hikari\application;

use \hikari\autoload\Autoload as Autoload;
use \hikari\component\Component as Component;
use \hikari\config\Php as PhpConfig;

abstract class ApplicationAbstract extends Component implements ApplicationInterface {
    public $config = [];
    public $path;

    public function __construct(array $properties = array()) {
        $this->application = $this;
        if(empty($properties['path'])) {
            ArgumentException::raise('$properties[path]');
        }
        $this->path = $properties['path'];
        unset($properties['path']);
        Autoload::push($this->path . '/..');
        if(empty($properties['config'])) {
            $configFile = $this->path . '/config/main.php';
            if(is_file($configFile)) {
                $config = new PhpConfig;
                $config->load($configFile);
                $properties['config'] = $config;
            }
        }
        parent::__construct($properties);
        #if($this->applicationConfig) {
        #    $this->config->merge($this->applicationConfig);
        #}
    }
    
    public abstract function run();
}
