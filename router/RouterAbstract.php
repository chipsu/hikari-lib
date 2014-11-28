<?php

namespace hikari\router;

use \hikari\component\Component as Component;
use \hikari\core\Uri as Uri;

abstract class RouterAbstract extends Component implements RouterInterface {
    public $routes = [];
    public $cache;

    function initialize() {
        if($this->cache && $this->cache->value([$this->application->config->hash, __METHOD__], $routes)) {
            $this->routes = $routes;
        } else {
            foreach($this->routes as $name => $route) {
                if(!$route instanceof Route) {
                    empty($route['name']) and $route['name'] = $name;
                    $this->routes[$name] = new Route($route);
                }
            }
            if($this->cache) {
                $this->cache->set([$this->application->config->hash, __METHOD__], $this->routes);
            }
        }
    }

    function route($request) {
        if($this->cache) {
            $cacheKey = [__METHOD__, $request];
            if($this->cache->value($cacheKey, $result)) {
                return $result;
            }
        }
        foreach($this->routes as $route) {
            if($match = $route->match($request)) {
                $result = clone $request;
                $result->get = array_merge($result->get, $match);
                $result->request = array_merge($result->request, $match);
                if($route->forward) {
                    $request = $result;
                    continue;
                }
                $this->cache and $this->cache->set($cacheKey, $result);
                return $result;
            }
        }
        \hikari\exception\NotFound::raise();
    }

    function build($name, array $parameters = []) {
        if($this->cache) {
            $cacheKey = [$this->application->config->hash, __METHOD__, $name, json_encode($parameters)];
            if($this->cache->value($cacheKey, $result)) {
                return $result;
            }
        }
        foreach($this->routes as $route) {
            if($result = $route->build($name ?: 'default', $parameters)) {
                if($this->cache) {
                    $this->cache->set($cacheKey, $result);
                }
                return $result;
            }
        }
        $result = new Uri;
        $result->query = http_build_query($parameters);
        if($this->cache) {
            $this->cache->set($cacheKey, $result);
        }
        return $result;
    }
}
