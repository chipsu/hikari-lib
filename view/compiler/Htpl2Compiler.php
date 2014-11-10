<?php

namespace hikari\view\compiler;

use \hikari\exception\Core as ParseError;


/*


optimization:
    tag('div', ['id' => 'asdfasdf'])
        => <div id="...">

*/
abstract class Htpl2Generator {
    public $compiler;

    function __construct($compiler) {
        $this->compiler = $compiler;
    }

    function node(Node $node) {
        if(!isset($node->data['type'])) {
            #echo __METHOD__;
            #var_dump($node->data);
            return '';
        }
        $method = '_' . $node->data['type'];
        $node->data['indentation'] = str_repeat('    ' , isset($node->data['indent']) ? $node->data['indent'] : 0);
        $source = isset($node->data['source']) ? $node->data['source'] : '<NULL>';
        return sprintf($node->data['indentation'] . '// %s:', $node->data['type']) . ' | sauce: ' . $source . PHP_EOL . $node->data['indentation'] . $this->$method($node) . PHP_EOL;
    }

    function children(Node $node) {
        $result = [];
        $result[] = sprintf('// %s -> %d children:', $node->data['type'], count($node->children));
        foreach($node->children as $child) {
            if(empty($child->data['type'])) {
                var_dump($child->data);
                die;
            }
            $indent = str_repeat('    ' , isset($child->data['indent']) ? $child->data['indent'] : 0);
            $result[] = $indent . sprintf('// child %s start:', $child->data['type']);
            $result[] = $this->node($child);
            $result[] = $indent . sprintf('// child %s end:', $child->data['type']);
        }
        return implode(PHP_EOL, $result) . PHP_EOL;
    }

    function _root(Node $node) {
        return $this->children($node);
    }

    abstract function _element(Node $node);
    abstract function _for(Node $node);
    abstract function _if(Node $node);
    abstract function _else(Node $node);
    abstract function _call(Node $node);
}

class Htpl2GeneratorPhp extends Htpl2Generator {

    function run($node) {
        return '<?php ' .
               'namespace _anon_' . sha1(uniqid()) . '_; ' .
               '$html = new \hikari\html\Html; ' .
               '$api = new \hikari\view\htpl\Htpl; ' .
               $this->node($node);
    }

    function __emit($function, array $args) {
        return sprintf('$api.%s()', $function);
    }

    function _element(Node $node) {
        $tag = $node->data['tag'];
        $args = '';
        foreach($node->data['args'] as $key => $value) {
            if(is_array($value)) {
                if(!isset($value['expression'])) {
                    echo '<pre>';
                    var_dump($value);
                    die;
                }
                $value = $this->parseExpression($value['expression']);
            }
            if($value !== null) {
                $args .= sprintf('%s => %s, ', $key, $value);
            }
        }
        $args = rtrim($args, ', ');
        return sprintf('echo $html->open("%s", [%s]);', $tag, $args) . $this->children($node)  . $node->data['indentation'] . sprintf('echo $html->close("%s");', $tag);
    }

    function _for(Node $node) {
        $expression = sprintf('%s as %s => %s', $this->parseExpression($node->data['expression']['expression']), $node->data['key'], $node->data['value']);
        return 'foreach(' . $expression . ') {' . $this->children($node) . $node->data['indentation'] . '}';
    }

    function _if(Node $node) {
        $expression = $this->parseExpression($node->data['expression']['expression']);
        return 'if(' . $expression . ') {' . $this->children($node) . $node->data['indentation'] . '}';
    }

    function _else(Node $node) {
        return 'else {' . $this->children($node) . $node->data['indentation'] . '}';
    }

    function _call(Node $node) {
        return '_call';
    }

    function _echo(Node $node) {
        return $this->_expression($node);
    }

    function _expression(Node $node) {
        $expression = $this->parseExpression($node->data['expression']);
        return sprintf('echo %s;', $expression);
    }

    function parseExpression($expression) {
        $expression = $this->compiler->encodeStrings($expression);
        $expression = preg_replace('/([a-zA-Z]+)(\.)(\w+)/', '${1}->${3}', $expression); // dot calls to ->
        return $this->compiler->decode($expression);
    }
}


#class Htpl2GeneratorJavaScript {
#}

class Node {
    public $data;
    public $parent;
    public $children = [];

    function __construct($data = null) {
        $this->data = $data;
    }

    function add(Node $node) {
        assert($node->parent == null);
        printf("add: %s to %s -- %s\n", $node->data['type'], $this->data['type'], $this->data['source']);
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

    function tree() {
        $result = $this->data;
        foreach($this->children as $key => $value) {
            $result['$children'][$key] = $value->tree();
        }
        return $result;
    }
}

/**
 * Quick regex hack HTPL compiler
 *
 * @todo Attribute values like function calls are not parsed correctly. Example "div @id=$value.id". Temporary solution: "div @id=($value.id)"
 * @todo Inline function arguments are not parsed correcly, should be separated with a space (like arrays), not comma.
 * @todo Named parameters for functions, like func("1" "2" fourth="4")
 * @todo String concatenation. PHP uses ., JS uses +, HTPL uses ???. We can't really detect the type.. Use . and translate JS?
 * @todo Optimization: Elemnts with no dynamic code should be optimized into a single echo, instead of $html->tag().
 */
class Htpl2Compiler extends CompilerAbstract {
    public $index;
    public $lines;
    public $indent;
    public $indentSize;
    public $root;
    public $node;
    public $attributePrefix = [
        '@' => 'data-',
        '&' => 'hui-',
    ];

    function source($source, array $options = []) {
        //header('content-type: text/plain');
        $source = preg_replace("/(\s*\\\\\n\s*)/", ' ', $source); # combine lines ending with \
        $this->lines = explode(PHP_EOL, $source);
        $this->index = 0;
        $this->indent = 0;
        $this->root = new Node([
            'type' => 'root',
            'source' => '<root>',
        ]);
        $this->node = $this->root;
        while($this->index < count($this->lines)) {
            if($result = $this->parseLine()) {
                $node = new Node($result);
                $diff = $result['indent'] - $this->indent;
                #printf("node: %s:%d\n", $result['type'], $diff);
                if($diff) {
                    if($diff < 0) {
                        while($diff++ < 0) {
                            assert($this->node->parent);
                            $this->node = $this->node->parent;
                        }
                        #printf("pop to: %s : %s\n", $this->node->data['type'], $this->node->data['source']);
                        $this->node->add($node);
                    } else if($diff == 1) {
                        $this->node = $this->node->children[count($this->node->children) - 1];
                        $this->node->add($node);
                    } else {
                        ParseError::raise('Parse error on line %d: "%s" : Too much indentation (from %d to %d)', $result['index'], $result['source'], $this->indent, $result['indent']);
                    }
                    $this->indent = $result['indent'];
                } else {
                    $this->node->add($node);
                }
            }
        }
        $output = isset($options['output']) ? $options['output'] : 'php';
        switch($output) {
        case 'raw':
            return $this->root;
        case 'json':
            return json_encode($result, $this->debug ? \JSON_PRETTY_PRINT : 0);
        case 'object':
            return (object)$result;
        case 'php':
            $generator = new Htpl2GeneratorPhp($this);
            $result = $generator->run($this->root);
            file_put_contents('/tmp/tpl.php', $result . PHP_EOL . '/*' . print_r($this->root->tree(), true));
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

        foreach(['parseElement', 'parseCondition', 'parseEcho', 'parseExpression'] as $method) {
            if($node = $this->$method($line)) {
                $node['source'] = $source;
                $node['indent'] = $indent;
                $node['index'] = $this->index;
                $node['method'] = $method;
                return $node;
            }
        }

        ParseError::raise('Parse error on line %d: "%s"', $this->index, $line);
    }

    /**
     * Parse an element
     *
     * Format: tag#id.class
     */
    function parseElement($line) {
        if(preg_match('/^(?<tag>\w+)(?<id>#[\-\w]+|)(?<class>\.[\.\-\w]+|)/', $line, $match)) {
            $args = trim(substr($line, strlen($match[0])));
            $args = $this->parseArguments($args);
            $args = array_merge([
                '"id"' => empty($match['id']) ? null : '"' . trim($match['id'], '#') . '"',
                '"class"' => empty($match['class']) ? null : '"' . trim(implode(' ', explode('.', $match['class']))) . '"',
            ], $args);
            return [
                'type' => 'element',
                'tag' => $match['tag'],
                'args' => $args,
            ];
        }
        return false;
    }

    // %expression
    function parseEcho($line) {
        $pattern = $this->compileRegex('/\s*(%.*)|(${constant})/');
        if(preg_match($pattern, $line, $match)) {
            $result = $this->parseExpression(ltrim($match[0], '%'));
            $result['type'] = 'echo';
            return $result;
        }
        return false;
    }

    // grammar
    //   operators: + - * / = !=  == etc..
    //   array operators: []
    //   expression group: ()
    //   $variables & constants: "strings", 'strings', 1243214 or 324.3245
    //   function call: expression(moreStuff)
    // arrays:
    //   key1=value1, key2=value2
    //   where both key and value can be:
    //     constant \w\_+
    //     html-attribute-name (- allowed here)
    //     "quoted string" or 'single quoted string'
    //     $variable
    //     $variable.property
    //     $variable.method()
    //     function()
    //     $functor()
    //     @lazydata translates to data-lazy
    //     [prefix]attr-name - works the same way as above, but fetches prefix from config - example &menu-id > hui-menu-id
    //   value can also be:
    //     an array, like: key=[a=1 b=2 c=[3...]]
    //     an expression that will be evaluated: key=($var + 1) or key=([array] + [otherArray])
    protected function patterns() {
        return [
            'attributePrefix' => '(' . implode('|', array_map('preg_quote', array_keys($this->attributePrefix))) . ')',
            'integer' => '[\d]+',
            'float' => '[\d]+\.[\d]+',
            'string' => '("(?:\\\"|.)*?")|(\\\'(?:\\\\\'|.)*?\\\')',
            'encoded' => '(' . chr(1) . '(\d+)' . chr(2) . ')',
            'constant' => '(${string}|${encoded}|${float}|${integer})',
            'variable' => '\$[\w\d]+',
            'attribute' => '(${attributePrefix}[\w\-]+)|([\w\-]+)',
            'expression' => '(${subexpression}|${arguments})',
            'subexpression' => $this->balancedRegex('(', ')'),
            'arguments' => $this->balancedRegex('[', ']'),
        ];
    }

    protected function balancedRegex($open, $close) {
        $pattern = '(A((?>[^AB]+)|(?-2))*B)';
        return str_replace(['A', 'B'], [preg_quote($open), preg_quote($close)], $pattern);
    }

    protected function compileRegex($pattern) {
        $bits = $this->patterns();
        $result = $pattern;
        do {
            $count = 0;
            $result = preg_replace_callback('/\$\{(?<key>\w+)\}/', function($match) use($bits, &$count) {
                $count++;
                $key = $match['key'];
                if(!isset($bits[$key])) {
                    throw new \Exception('Undefined key "' . $key . '"');
                }
                return $bits[$key];
            }, $result);
        } while($count > 0);
        return $result;
    }

    protected $encoded = [];

    function encodeStrings($text) {
        return $this->encode($text, $this->compileRegex('/${string}/'));
    }

    // TODO: Tag different types (string & other) with different prefix & suffix
    function encode($text, $pattern) {
        return preg_replace_callback($pattern, function($match) {
            $key = count($this->encoded);
            $this->encoded[] = $match[0];
            return chr(1) . $key . chr(2);
        }, $text);
    }

    function decode($text) {
        return preg_replace_callback('/' . chr(1) . '(\d+)' . chr(2) . '/', function($match) {
            return $this->encoded[$match[1]];
        }, $text);
    }

    function parseArguments($line) {
        $line = $this->encodeStrings($line);
        $pattern = $this->compileRegex('/(?<key>(?<variable>${variable})|(?<constant>${constant})|(?<attribute>${attribute})|(${expression}))\=(?<value>(?<valueVariable>${variable})|(?<valueConstant>${constant})|(?<valueExpression>${expression}))/');
        if(preg_match_all($pattern, $line, $match)) {
            $keys = $match['key'];
            $values = $match['value'];
            foreach($match['attribute'] as $index => $value) {
                if(strlen($value)) {
                    $keys[$index] = "'" . str_replace(array_keys($this->attributePrefix), array_values($this->attributePrefix), $value) . "'";
                }
            }
            foreach($match['valueExpression'] as $index => $value) {
                if(strlen($value)) {
                    $values[$index] = $this->parseExpression($value);
                }
            }
            $keys = array_map(array($this, 'decode'), $keys);
            $values = array_map(array($this, 'decode'), $values);
            $args = array_combine($keys, $values);
            return $args;
        }
        return [];
    }

    function parseExpression($line) {
        $expression = $line;
        return [
            'type' => 'expression',
            'expression' => $expression,
        ];
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
