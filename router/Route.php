<?php

namespace hikari\router;

use \hikari\component\Component as Component;

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
                // CamelCase Controller temp fix.
                $replace = function($variable) use($match) {
                    $key = strtolower($variable[1]);
                    $replace = $match[$key];
                    if(strtoupper($key[0]) == $variable[1][0]) {
                        $replace = ucfirst($replace);
                    }
                    return $replace;
                };
                foreach($this->target as $key => $value) {
                    if($value instanceof \Closure) {
                        $value = call_user_func($value, $this);
                    }
                    if(!isset($match[$key])) {
                        $match[$key] = $value;
                    }   
                    $match[$key] = preg_replace_callback('/\:([\w]+)/', $replace, $match[$key]);
                }
                return $match;
            }
            continue;
            if(preg_match($regexp, $request->uri->path, $match)) {
                // CamelCase Controller temp fix.
                $replace = function($variable) use($match) {
                    $key = strtolower($variable[1]);
                    $replace = $match[$key];
                    if(strtoupper($key[0]) == $variable[1][0]) {
                        $replace = ucfirst($replace);
                    }
                    return $replace;
                };
                foreach($this->target as $key => $value) {
                    if($value instanceof \Closure) {
                        $value = call_user_func($value, $this);
                    }
                    if(!isset($match[$key])) {
                        $match[$key] = $value;
                    }   
                    $match[$key] = preg_replace_callback('/\:([\w]+)/', $replace, $match[$key]);
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
        foreach($this->format as $index => $format) {
            # $uri = new Uri
            # foreach parts
            #   $uri->$part = preg_replace($pattern, $parameters)
            #   if(!preg_match($this->regexp[$index][$part], $uri->$part))
            #      $uri = false
            #      break
            # if($uri)
            #   return $uri
            foreach($format as $part => $pattern) {
                if($part == 'domain') {
                    return 'http://' . $pattern;
                }
                // if has all $parameters
                // use regexp?
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
                    $replace = function($match) {
                        return sprintf('(?<%s>[\w]+)', $match[1]);
                    };
                    $regexp[$part] = '/^' . preg_replace_callback('/\\\:([\w]+)/', $replace, preg_quote($pattern, '/')) . '$/';
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
}