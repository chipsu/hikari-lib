<?php

namespace hikari\view\compiler;

use \hikari\exception\Core as ParseError;


/*


optimization:
    tag('div', ['id' => 'asdfasdf'])
        => <div id="...">

*/
abstract class Htpl2Generator {
    abstract function __emit($function, array $args);
}

class Htpl2GeneratorPhp {
    function __emit($function, array $args) {
        return sprintf('$api.%s()', $function);
    }

}

#class Htpl2GeneratorJavaScript {
#}

class Htpl2Compiler extends CompilerAbstract {
    public $index;
    public $lines;
    public $indentSize;

    function source($source, array $options = []) {
        header('content-type: text/plain');
        $this->lines = explode(PHP_EOL, $source);
        $this->index = 0;
        while($this->index < count($this->lines)) {
            $this->parseLine();
        }
        var_dump($source);
        die(__METHOD__);
        $output = isset($options['output']) ? $options['output'] : 'php';
        switch($output) {
        case 'array':
            return $result;
        case 'json':
            return json_encode($result, $this->debug ? \JSON_PRETTY_PRINT : 0);
        case 'object':
            return (object)$result;
        case 'php':
            $compiler = new JtplCompiler;
            $result =  $compiler->source($result);
            var_dump($result);
            die;
            return $result;
        default:
            NotSupported::raise($output);
        }
    }

    function store($fileName, $result) {
        file_put_contents($fileName, is_string($result) ? $result : json_encode($result));
    }

    function parseLine() {
        $line = $this->lines[$this->index++];
        $line = rtrim($line);
        $trim = ltrim($line);
        $indent = 0;

        if(!strlen($trim) || $trim[0] == '#') {
            return;
        }

        if(preg_match('/^(?<indent>\s+|)/', $line, $match)) {
            if($indent = strlen($match['indent'])) {
                $line = substr($line, $indent);
                if($this->indentSize === null) {
                    $this->indentSize = $indent;
                }
                if($indent % $this->indentSize != 0) {
                    ParseError::raise('Parse error on line %d: "%s" : Uneven indentation', $this->index, $line);
                }
                $indent = $indent / $this->indentSize;
            }
        }

        if(preg_match('/^(?<tag>\w+)(?<id>#\w+|)(?<class>\.\w+|)/', $line, $match)) { // tag#id.class
            $name = $match[0];
            $args = $this->parseArgs(substr($line, strlen($match[0])));
            printf("tag: %s(%s)\n", $name, json_encode($args));
        } else if(preg_match('/^%(?<class>\w+)\.(?<method>\w+)/', $line, $match) || preg_match('/^%(?<method>\w+)/', $line, $match)) { // %class.func | %func
            $name = substr($match[0], 1);
            $args = substr($line, strlen($match[0]));
            $method = 'parse' . $name;
            if(method_exists($this, $method)) {
                $args = $this->$method($args);
                printf("ctrl: %s(%s)\n", $name, json_encode($args));
            } else {
                $args = $this->parseArgs($args);
                printf("call: %s(%s)\n", $name, json_encode($args));
            }
        } else if($expression = $this->parseExpression($line)) {
            printf("expr: %s\n", $expression);
        } else {
            ParseError::raise('Parse error on line %d: "%s"', $this->index, $line);
        }
    }

    // key1=value1, key2=value2
    // where both key and value can be:
    //   constant \w\_+
    //   html-attribute-name (- allowed here)
    //   "quoted string" or 'single quoted string'
    //   $variable
    //   $variable.property
    //   $variable.method()
    //   function()
    //   $functor()
    //   @lazydata translates to data-lazy
    //   [prefix]attr-name - works the same way as above, but fetches prefix from config - example &menu-id > hui-menu-id
    // value can also be:
    //   an array, like: key=[a=1 b=2 c=[3...]]
    //   an expression that will be evaluated: key=($var + 1) or key=([array] + [otherArray])
    function parseArgs($string) {
        return ['poopface'];
    }

    // parse an expression
    //   operators: + - * / = !=  == etc..
    //   array operators: []
    //   expression group: ()
    //   $variables & constants: "strings", 'strings', 1243214 or 324.3245
    //   function call: expression(moreStuff)
    function parseExpression($string) {

    }

    function parseIf($string) {

    }

    function parseElseIf($string) {

    }

    function parseElse($string) {

    }

    function parseFor($string) {

    }
}
