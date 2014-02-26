<?php

namespace hikari\bootstrap;

use \hikari\autoload\Autoload as Autoload;

class Bootstrap {

	static function app(array $properties) {
		assert(is_dir($properties['path']));
        Autoload::push($properties['path'] . '/..');
        Autoload::push($properties['path'] . '/lib'); // TODO: Autoload config
        $name = isset($properties['name']) ? $properties['name'] : basename($properties['path']);
        $class = isset($properties['class']) ? $properties['class'] : '\\' . $name .'\application\Application';
		$app = new $class($properties);
		return $app->run();
	}
}
