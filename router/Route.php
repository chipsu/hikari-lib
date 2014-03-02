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
        foreach($this->regexp as $index => $regexp) {
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

    function compile() {
        if(empty($this->target)) {
            \hikari\exception\Argument('No target set');
        }
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
            foreach($this->format as $format) {
                $replace = function($match) {
                    return sprintf('(?<%s>[\w]+)', $match[1]);
                };
                $regexp = '/^' . preg_replace_callback('/\\\:([\w]+)/', $replace, preg_quote($format, '/')) . '$/';
                $this->regexp[] = $regexp;
            }
        } else if(is_string($this->regexp)) {
            $this->regexp = [$this->regexp];
        }
    }
}