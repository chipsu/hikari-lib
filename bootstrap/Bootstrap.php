<?php

namespace hikari\bootstrap;

class Bootstrap {

	static function app(array $parameters) {
		$app = new \hikari\application\Application($parameters);
		return $app->run();
	}
}
