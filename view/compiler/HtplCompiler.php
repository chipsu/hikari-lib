<?php

namespace hikari\view\compiler;

class HtplCompiler extends CompilerAbstract {
    function source($source, array $options = []) {
        $result = [
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
            return $compiler->source($result);
        default:
            NotSupported::raise($output);
        }
    }
    function store($fileName, $result) {
        file_put_contents($fileName, is_string($result) ? $result : json_encode($result));
    }
}

