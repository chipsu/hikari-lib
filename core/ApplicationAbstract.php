<?php

namespace hikari\core;

use \hikari\autoload\Autoload as Autoload;
use \hikari\config\Php as PhpConfig;

abstract class ApplicationAbstract extends Component implements ApplicationInterface {
    public $config = [];
    public $configHash;
    public $path;
    public $publicPath;
    public $runtimePath;
    public static $instance;

    function __construct(array $properties = array()) {
        static::$instance = $this;
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

    public function expand($string) {
        $map = [
            '@app' => $this->application->path,
            '@lib' => realpath($this->application->path . '/../metrica/hikari-lib'),
            '@vendor' => realpath($this->application->path . '/../vendor'),
            '@role' => 'admin',
        ];
        return str_replace(array_keys($map), array_values($map), $string);
    }

    public function expandArray(array $array) {
        foreach($array as &$string) {
            $string = $this->expand($string);
        }
        return $array;
    }

    public abstract function run();
}
