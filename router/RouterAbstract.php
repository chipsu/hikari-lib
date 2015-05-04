<?php

namespace hikari\router;

use \hikari\core\Component;
use \hikari\core\Uri;

abstract class RouterAbstract extends Component implements RouterInterface {
    private $_groups = [];
    public $controllerMap = [];
    public $cache;
    private $_cachePrefix;

    public $routes_OLD;

    public function __construct(array $properties) {
        $groups = isset($properties['groups']) ? $properties['groups'] : [];
        unset($properties['groups']);
        parent::__construct($properties);
        $this->cache = false; ## FIX
        if($this->cache && $this->cache->value($cacheKey = $this->getCachePrefix(__METHOD__), $groups)) {
            $this->_groups = $groups;
            if($this->cache) {
                $this->cache->set($cacheKey, $this->_groups);
            }
        } else {
            $this->setGroups($groups);
        }
    }

    public function init() {
        parent::init();
    }

    public function setGroups($groups) {
        foreach($groups as $name => $group) {
            if(!$group instanceof RouteGroup) {
                empty($group['name']) and $group['name'] = $name;
                $groups[$name] = new RouteGroup($group);
            }
        }
        $this->_groups = $groups;
    }

    public function getGroups() {
        return $this->_groups;
    }

    public function setCachePrefix($value) {
        $this->_cachePrefix = $value;
    }

    public function getCachePrefix() {
        if($this->_cachePrefix === null) {
            $this->_cachePrefix = $this->application->config->hash;
        }
        return $this->_cachePrefix;
    }

    protected function getCacheKey($suffix) {
        return [$this->getCachePrefix(), $suffix];
    }

    public function route($request) {
        if($this->cache) {
            $cacheKey = $this->getCacheKey([_METHOD__, $request]);
            if($this->cache->value($cacheKey, $result)) {
                return $result;
            }
        }
        foreach($this->groups as $group) {
            if($match = $group->match($request)) {
                $result = clone $request;
                $result->queryParams = array_merge($result->queryParams, $match);
                $this->cache and $this->cache->set($cacheKey, $result);
                return $result;
            }
        }
        \hikari\exception\NotFound::raise();
    }

    public function build($name, array $parameters = []) {
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
