<?php

namespace hikari\autoload;

class Autoload {
	public static $load = [__CLASS__, 'load'];
	public static $paths = [];

	public static function load($class) {
		foreach(static::$paths as $path) {
        	$file = $path . '/' . str_replace('\\', '/', $class) . '.php';
        	if(is_file($file)) {
	            require_once($file);
	            return;
	        }
		}
	}

	// TODO: push prefix?
	public static function push($path) {
		array_unshift(static::$paths, $path);
	}
}