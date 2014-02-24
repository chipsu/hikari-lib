<?php

require_once __DIR__ . '/../autoload.php';

namespace \hikari\bootstrap;

class Bootstrap {

	public function dir($dir) {
		return $this->app(['path' => $dir . '/../app']);
	}

	public function app(array $parameters) {
		$app = new \hikari\application\Application($parameters);
		return $app->run();
	}
}

$bootstrap = new Bootstrap;
return $bootstrap;
