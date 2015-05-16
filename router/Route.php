<?php

namespace hikari\router;

use \hikari\core\Component;
use \hikari\core\Uri;

class Log {
    static function trace() {
        if(!headers_sent())
            header('content-type: text');
        $args = func_get_args();
        foreach($args as &$arg) {
            if(is_object($arg)) {
                $arg = (string)$arg;
            } else if(is_array($arg)) {
                $arg = print_r($arg, true);
            }
        }
        call_user_func_array('printf', $args);
        echo PHP_EOL;
    }
}

/**
 * @class
 *
 * Short-config: [path, methods, defaults]
 * ['/:controller/:id', 'get,post', ['id' => null]]
 * Same as: ['path' => '/:controller/:id', 'methods' => ['get', 'post'], ['id' => null]]
 */
class Route extends Component {
    private $_regexps;
    private $_formats;
    public $defaults;
    ///public $import;
    ///public $target;
    ///public $forward;
    public $propertyFilters = ['propertyFilter'];

    public function __construct(array $properties = []) {
        parent::__construct($properties);
    }

    public function propertyFilter(array &$properties) {
        $map = ['path', 'methods', 'defaults'];
        $values = [];
        foreach($properties as $key => $value) {
            if(is_numeric($key)) {
                $values[] = $value;
                unset($properties[$key]);
            }
        }
        if(count($values) > count($map)) {
            CoreException::raise('Too many quick-properties!');
        }
        foreach($values as $value) {
            $properties[array_shift($map)] = $value;
        }
    }

    public function init() {
        parent::init();
    }

    public function getMethods() {
        return $this->getFormat('http:method');
    }

    public function setMethods($methods) {
        if(is_string($methods)) {
            $methods = str_replace('@rest', 'head,options,get,put,post,patch,delete', $methods); # TODO: Real aliases
            $methods = strtoupper($methods);
            $methods = explode(',', $methods);
        }
        return $this->setFormat('http:method', $methods);
    }

    public function getScheme() {
        return $this->getFormat('uri:scheme');
    }

    public function setScheme($scheme) {
        return $this->setFormat('uri:scheme', $scheme);
    }

    public function getPath() {
        return $this->getFormat('uri:path');
    }

    public function setPath($path) {
        return $this->setFormat('uri:path', $path);
    }

    public function getHost() {
        return $this->getFormat('uri:host');
    }

    public function setHost($host) {
        return $this->setFormat('uri:host', $path);
    }

    public function getFormats() {
        return $this->_formats;
    }

    public function setFormats(array $formats) {
        $this->_formats = [];
        return $this->addFormats($formats);
    }

    public function addFormats(array $formats) {
        foreach($formats as $part => $format) {
            $this->setFormat($part, $format);
        }
        return $this;
    }

    public function getFormat($part, $default = null) {
        return isset($this->_formats[$part]) ? $this->_formats[$part] : $default;
    }

    public function setFormat($part, $format) {
        $regexp = $this->compilePatterns($format);
        $this->_formats[$part] = $format;
        $this->_regexps[$part] = $regexp;
        return $this;
    }

    public function getRegexps() {
        return $this->_regexps;
    }

    public function setRegexps(array $regexps) {
        $this->_formats = [];
        $this->_regexps = [];
        foreach($regexps as $part => $regexp) {
            $this->setRegexp($part, $regexp);
        }
        return $this;
    }

    public function setRegexp($part, $regexp) {
        unset($this->formats[$part]);
        $this->_regexps[$part] = $regexp;
        return $this;
    }

    public function getRegexp($part, $default = null) {
        return isset($this->_regexps[$part]) ? $this->_regexps[$part] : $default;
    }

    public function getRequestPart($request, $part) {
        $parts = explode(':', $part, 2);
        switch($parts[0]) {
        case 'http':
            return $request->{$parts[1]};
        case 'uri':
            return $request->uri->{$parts[1]};
        }
        CoreException::raise();
    }

    public function match($request) {
        if(HI_LOG) Log::trace('%s: Try: %s', __METHOD__, $request);
        $matches = [];
        foreach($this->regexps as $part => $regexp) {
            $subject = $this->getRequestPart($request, $part);
            if(!preg_match($regexp, $subject, $match)) {
                if(HI_LOG) Log::trace('%s:   Failed! part="%s" regexp="%s" subject="%s"', __METHOD__, $part, $regexp, $subject);
                return false;
            }
            if(HI_LOG) Log::trace('%s:   Match! part="%s" regexp="%s" subject="%s": "%s"', __METHOD__, $part, $regexp, $subject, $match);
            $matches[] = $match;
        }
        if(HI_LOG) Log::trace('%s:   Success!: "%s"', __METHOD__, $matches);
        var_dump($matches);
        die;
        return false;
        /*foreach($this->regexps as $part => regexp) {
            $matches = [];
            foreach($parts as $part => $regexp) {
                if(!preg_match($regexp, $request->uri->$part, $match)) {
                    $matches = false;
                    break;
                }
                $matches[] = $match;
            }
            if($matches) {
                $match = call_user_func_array('array_merge', $matches);
                foreach($this->target as $key => $value) {
                    if($value instanceof \Closure) {
                        $value = call_user_func($value, $this);
                    }
                    if(!isset($match[$key])) {
                        $match[$key] = $value;
                    }
                    $match[$key] = $this->replaceParameters($match[$key], $match);
                }
                return $match;
            }
        }
        return false;*/
    }

    function build($name, array $parameters) {
        if($name != $this->name) {
            return false;
        }
        if(!$this->format) {
            return false;
        }
        if($this->target) {
            $parameters = array_merge($this->target, array('controller' => null), $parameters);
        }
        foreach($this->format as $index => $format) {
            $uri = [];
            $keys = [];
            foreach($format as $part => $pattern) {
                $uri[$part] = $this->replaceParameters($pattern, $parameters, $keys);
                if(!preg_match($this->regexp[$index][$part], $uri[$part])) {
                    $uri = false;
                    break;
                }
            }
            if($uri) {
                $uri['query'] = array_diff_key($parameters, $keys);
                foreach($this->target as $key => $value) {
                    if(isset($uri['query'][$key]) && strcmp($uri['query'][$key], $value) == 0) {
                        unset($uri['query'][$key]);
                    }
                }
                return new Uri($uri);
            }
        }
        return false;
    }

    function compile() {
        foreach(array('controller', 'action') as $index => $name) {
            if(isset($this->target[$index])) {
                $this->target[$name] = $this->target[$index];
                unset($this->target[$index]);
            }
        }
        if(empty($this->regexp)) {
            if(empty($this->format)) {
                \hikari\exception\Argument('Route need at least one Format or Regexp');
            }
            if(is_string($this->format)) {
                $this->format = [$this->format];
            }
            foreach($this->format as &$format) {
                if(is_string($format)) {
                    $format = ['path' => $format];
                }
                $regexp = [];
                foreach($format as $part => $pattern) {
                    $regexp[$part] = $this->compilePattern($pattern);
                }
                $this->regexp[] = $regexp;
            }
        } else if(is_string($this->regexp)) {
            $this->regexp = [$this->regexp];
            foreach($this->regexp as &$regexp) {
                if(is_string($regexp)) {
                    $regexp = ['path' => $regexp];
                }
            }
        }
    }

    function replaceParameters($subject, array $parameters, &$keys = null) {
        // CamelCase Controller temp fix.
        $callback = function($variable) use($parameters, &$keys) {
            $key = strtolower($variable[1]);
            $result = false;
            if(isset($parameters[$key])) {
                $result = $parameters[$key];
                if(strtoupper($key[0]) == $variable[1][0]) {
                    $result = ucfirst($result);
                }
                if($keys !== null) {
                    $keys[$key] = $key;
                }
            }
            return $result;
        };
        return preg_replace_callback('/\:(?<name>[\w]+)(?<type>\([\w]+\)|)/', $callback, $subject);
    }

    function compilePatterns($patterns) {
        is_string($patterns) and $patterns =  [$patterns];
        foreach($patterns as &$pattern) {
            $callback = function($match) {
                $types = [
                    'string' => '\w\-_',
                    'alpha' => '\w',
                    'int' => '\d',
                ];
                $type = trim($match['type'], ' ()\\');
                $type = isset($types[$type]) ? $types[$type] : $types['string'];
                return sprintf('(?<%s>[%s]+)', $match['name'], $type);
            };
            $search = '/\\\:(?<name>[\w]+)(?<type>\\\\\([\w]+\\\\\)|)/';
            try {
                $pattern = preg_quote($pattern, '/');
                $pattern = preg_replace_callback($search, $callback, $pattern);
            } catch(\Exception $ex) {
                throw \hikari\exception\Core::raise('Could not compile pattern: "%s", error: "%s"', $pattern, $ex->getMessage());
            }
        }
        $pattern = implode('|', $patterns);
        $regexp = '/^' . $pattern . '$/';
        return $regexp;
    }
}
