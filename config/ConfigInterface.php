<?php

namespace hikari\config;

interface ConfigInterface extends \hikari\storage\StorageInterface {

    /**
     * Load config from other StorageInterface or Array.
     * @param  StorageInterface|Array $config -
     * @return ConfigInterface -
     */
    function load($config);
    
    /**
     * Merge with other StorageInterface or Array.
     * @param StorageInterface|Array $config -
     * @param boolean $overwrite -
     * @return ConfigInterface -
     */
    function merge($config, $overwrite = false);
}
