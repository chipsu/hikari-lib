<?php

namespace hikari\autoload;

class Autoload {
	public static $load = [__CLASS__, 'load'];
	public static $paths = [];
	public static $classes = [];
	public static $cache;

	static function init() {
		// TODO: Use cache interface
		#$db = new \MongoClient();
		#static::$cache = $db->autoload->cache3;
		if(static::$cache) {
			static::$cache->ensureIndex(['key' => 1], ['unique' => 1]);
			if($entry = static::$cache->findOne(['key' => __FILE__])) {
				static::$classes = $entry['classes'];
			}
		}
	}

	static function load($class) {
		if(isset(static::$classes[$class])) {
			require_once(static::$classes[$class]);
			return true;
		}
		foreach(static::$paths as $path) {
        	$file = $path . '/' . str_replace('\\', '/', $class) . '.php';
        	if(is_file($file)) {
        		if(static::$cache) {
        			static::$classes[$class] = $file;
        			$values = [
        				'key' => __FILE__,
        				'classes' => static::$classes,
        			];
        			static::$cache->update(['key' => __FILE__], $values, ['upsert' => true]);
        			error_log('no cache: ' . $class);
        		}
	            require_once($file);
	            return true;
	        }
		}
		return false;
	}

	// TODO: push prefix?
	static function push($path) {
		array_unshift(static::$paths, $path);
	}
}