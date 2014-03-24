<?php

namespace hikari\view;

class HtplCompiler {

    function compileFile($fileName) {
        $source = file_get_contents($fileName);
        return $this->compile($source);
    }

    function compile($source) {
        return json_encode([
            ['tag' => 'h1', 'attr' => ['class' => 'header'], 'content' => '<u>Title: $title</u>'],
            ['tag' => 'pre', 'content' => $source],
            ['tag' => 'ul', 'children' => [
                ['type' => 'generator', 'content' => 'for($i=0; $i<5; ++$i)', 'children' =>  [
                    ['tag' => 'li', 'content' => '$i'],
                ],
                ['type' => 'generator', 'content' => 'foreach($items as $i)', 'children' =>  [
                    ['tag' => 'li', 'content' => 'Item: @($i,"<b>test</b>")'],
                ],
            ]],
        ]);
    }

}

// foreach($items as $i) =>
// php: same
// js: for(k in $items) { $i = $items[$k]; }
// todo: string concat how?
class JtplCompiler {

    function compile($source) {
        $result = [];
        $data = json_decode($source, true);
        $html = new \hikari\html\Html;
        foreach($data as $key => $value) {
            $attr = isset($value['attr']) ? $value['attr'] : [];
            $content = isset($value['content']) ? $value['content'] : null;
            $result[] = $html->tag($value['tag'], $attr, $content);
        }
        return implode(PHP_EOL, $result);
    }

}
