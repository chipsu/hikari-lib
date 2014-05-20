<?php

namespace hikari\view\compiler;

use \hikari\utilities\Lexer;
use \hikari\exception\Core as ParseError;

class HtplCompiler extends CompilerAbstract {
    function source($source, array $options = []) {
        $stack = [];
        $state = ['indent' => 0, 'tabsize' => 0, 'level' => 0];
        $lines = explode(PHP_EOL, $source);
        $lines[] = 'end of document';
        $result = [];
        $node = &$result;
        foreach($lines as $index => $line) {
            $line = rtrim($line);
            $trim = ltrim($line);
            if(!strlen($trim) || $trim[0] == '#') {
                continue;
            }
            if(!preg_match('/^(?<indent>\s+|)(?<tag>\w+)(?<id>#\w+|)(?<class>\.\w+|)(?<operator>[\=]|)\s*(?<content>.*+|)$/', $line, $match)) {
                ParseError::raise('Parse error on line %d: "%s"', $index + 1, $line);
            }
            var_dump($match);
            $indent = strlen($match['indent']);
            echo 'tag:'. $match['tag'].'<br>';
            if($indent != $state['indent']) {
                if($state['tabsize'] == 0) {
                    $state['tabsize'] = $indent;
                }
                $state['indent'] = $indent;
            }
            if($index + 1 == count($lines)) {
                $level = 0;
            } else {
                $level = $state['tabsize'] ? $indent / $state['tabsize'] : 0;
            }
            $match['level'] = $level;
            print_r("$level - $indent . " . $state['tabsize']);
            while(count($stack) > $level) {
                $parent = array_pop($stack);
                #echo '</' . $parent['tag'] . '>';
                $node = &$parent['node'];
            }
            if($match['tag'] == 'end') {
                break;
            }
            #echo '<' . $match['tag'] . '>';
            $attr = [
                'id' => ltrim($match['id'], '#'),
                'class' => ltrim($match['class'], '.'),
            ];
            $attr = array_filter($attr, 'strlen');
            $node[] = ['tag' => $match['tag'], 'attr' => $attr, 'content' => $match['content'], 'children' => []];
            $match['node'] = &$node;
            $node = &$node[count($node) - 1]['children'];
            $stack[] = $match;
            if($level >= count($stack)) {
                print_r("poo");
                if(count($stack) != $level) {
                    ParseError::raise('Parse error on line %d: %s - Bad indent', $index + 1, $line);
                }
                continue;
            }
            #var_dump($match);
            #$indent = substr_count(haystack, needle);
        }
        echo '<pre>';
        print_r($result);
        #die;
        $TEST = [
            ['type' => 'data', 'value' => ['title' => 'Default title', 'items' => ['hello', 'world']]],
            ['tag' => 'h1', 'attr' => ['class' => 'header'], 'content' => '<u>Title: $("title")</u>'],
            ['tag' => 'pre', 'content' => 'Content'],
            ['tag' => 'ul', 'children' => [
                ['type' => 'statement', 'statement' => 'for($i=0; $i<5; ++$i)', 'children' =>  [
                    ['tag' => 'li', 'content' => '$i'],
                ]],
                ['type' => 'statement', 'statement' => 'foreach($items as $i)', 'children' =>  [
                    ['tag' => 'li', 'content' => 'Item: @($i,"<b>test</b>")'],
                ]],
            ]],
        ];
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
            return $result;
        default:
            NotSupported::raise($output);
        }
    }
    function store($fileName, $result) {
        file_put_contents($fileName, is_string($result) ? $result : json_encode($result));
    }
}

