<?php

namespace hikari\application;

use \hikari\component\Component as Component;
use \hikari\config\Php as PhpConfig;

abstract class ApplicationAbstract extends Component implements ApplicationInterface {
    public $config = [];

    public function __construct(array $properties = array()) {
        $this->application = $this;
        if(empty($properties['config'])) {
            $configFile = HI_APP_PATH . 'config/' . HI_APP_CONFIG;
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
