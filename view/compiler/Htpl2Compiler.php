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

class Node {
    public $parent;
    public $children = [];
    public $data;

    function __construct($data = null) {
        $this->data = $data;
    }

    function add(Node $node) {
        assert($node->parent == null);
        $node->parent = $this;
        $this->children[] = $node;
        return $this;
    }

    function remove(Node $node) {
        foreach($this->children as $key => $value) {
            if($this->children[$key] === $value) {
                unset($this->children[$key]);
                return true;
            }
        }
        return false;
    }

    function detach() {
        if($this->parent) {
            $this->parent->remove($this);
        }
        return $this;
    }
}

class Htpl2Compiler extends CompilerAbstract {
    public $index;
    public $lines;
    public $indent;
    public $indentSize;
    public $root;
    public $node;

    function source($source, array $options = []) {
        header('content-type: text/plain');
        $this->lines = explode(PHP_EOL, $source);
        $this->index = 0;
        $this->indent = 0;
        $this->root = new Node([
            'type' => 'root',
        ]);
        $this->node = $this->root;
        while($this->index < count($this->lines)) {
            if($result = $this->parseLine()) {
                $node = new Node($result);
                $diff = $result['indent'] - $this->indent;
                if($diff) {
                    if($diff < 0) {
                        while($diff++ < 0) {
                            assert($this->node->parent);
                            $this->node = $this->node->parent;
                        }
                    } else if($diff == 1) {
                        $this->node->add($node);
                        $this->node = $node;
                    } else {
                        ParseError::raise('Parse error on line %d: "%s" : Too much indentation (from %d to %d)', $result['index'], $result['source'], $this->indent, $result['indent']);
                    }
                    $this->indent = $result['indent'];
                } else {
                    $this->node->add($node);
                }
            }
        }
        var_dump($this->root);
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
        $source = $this->lines[$this->index++];
        $line = $source;
        $line = rtrim($line);
        $trim = ltrim($line);
        $indent = 0;

        if(!strlen($trim) || $trim[0] == '#') {
            return false;
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

        $node = null;

        foreach(['parseElement', 'parseCondition', 'parseMethod', 'parseExpression'] as $method) {
            if($node = $this->$method($line)) {
                break;
            }
        }

        if(!$node) {
            ParseError::raise('Parse error on line %d: "%s"', $this->index, $line);
        }

        $node['source'] = $source;
        $node['indent'] = $indent;
        $node['index'] = $this->index;

        return $node;
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
        return [$string];
    }

    // tag#id.class
    function parseElement($line) {
        if(preg_match('/^(?<tag>\w+)(?<id>#\w+|)(?<class>\.\w+|)/', $line, $match)) {
            $name = $match[0];
            $args = $this->parseArgs(substr($line, strlen($match[0])));
            return [
                'type' => 'element',
                'args' => $args,
            ];
        }
        return false;
    }

    // %class.func | %func
    function parseMethod($line) {
        if(preg_match('/^%(?<class>\w+)\.(?<method>\w+)/', $line, $match) || preg_match('/^%(?<method>\w+)/', $line, $match)) {
            return $match;
        }
        return false;
    }

    // parse an expression
    //   operators: + - * / = !=  == etc..
    //   array operators: []
    //   expression group: ()
    //   $variables & constants: "strings", 'strings', 1243214 or 324.3245
    //   function call: expression(moreStuff)
    function parseExpression($line) {
        return $line;
    }

    function parseCondition($line) {
        if(preg_match('/^%(?<method>if |elseif |else|while )/', $line, $match)) {
            $expression = $this->parseExpression(substr($line, strlen($match[0])));
            return [
                'type' => trim($match['method']),
                'expression' => $expression,
            ];
        }
        if(preg_match('/^%(?<method>for )/', $line, $match)) {
            $statement = trim(substr($line, strlen($match[0])));
            if(preg_match('/^(?<key>\$\w+) (?<value>\$\w+) in /', $statement, $innerMatch) ||
               preg_match('/^(?<key>\$\w+) in /', $statement, $innerMatch)) {
                $expression = $this->parseExpression(substr($statement, strlen($innerMatch[0])));
                return [
                    'type' => trim($match['method']),
                    'key' => isset($innerMatch['key']) ? $innerMatch['key'] : null,
                    'value' => $innerMatch['value'],
                    'expression' => $expression,
                ];
            }
        }
        return false;
    }
}
