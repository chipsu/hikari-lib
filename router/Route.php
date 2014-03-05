<?php

namespace hikari\router;

use \hikari\component\Component as Component;
use \hikari\utilities\Uri as Uri;

class Route extends Component {
    public $name;
    public $regexp;
    public $format;
    public $target;
    public $import;
    public $forward;

    function __construct(array $parameters = []) {
        parent::__construct($parameters);
        $this->compile();
    }

    function match($request) {
        if(!$this->target) {
            return false;
        }
        foreach($this->regexp as $index => $parts) {
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
        return false;
    }

    function build($name, array $parameters) {
        if($name != $this->name) {
            return false;
        }
        if(!$this->format) {
            return false;
        }
        if($this->target) {
            $parameters = array_merge($parameters, $this->target);
        }
        foreach($this->format as $index => $format) {
            $uri = [];
            foreach($format as $part => $pattern) {
                $uri[$part] = $this->replaceParameters($pattern, $parameters);
                if(!preg_match($this->regexp[$index][$part], $uri[$part])) {
                    $uri = false;
                    break;
                }
            }
            if($uri) {
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

    function replaceParameters($subject, array $parameters) {
        // CamelCase Controller temp fix.
        $callback = function($variable) use($parameters) {
            $key = strtolower($variable[1]);
            $result = false;
            if(isset($parameters[$key])) {
                $result = $parameters[$key];
                if(strtoupper($key[0]) == $variable[1][0]) {
                    $result = ucfirst($result);
                }
            }
            return $result;
        };
        return preg_replace_callback('/\:([\w]+)/', $callback, $subject);
    }

    function compilePattern($pattern) {
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
        $pattern = preg_quote($pattern, '/');
        $pattern = preg_replace_callback($search, $callback, $pattern);
        return '/^' . $pattern . '$/';
    }
}
