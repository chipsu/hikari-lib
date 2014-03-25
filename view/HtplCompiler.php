<?php

namespace hikari\view;

class HtplCompiler {

    function compileFile($fileName) {
        $source = file_get_contents($fileName);
        return $this->compile($source);
    }

    function compile($source) {
        return json_encode([
            ['type' => 'data', 'value' => ['title' => 'Default title', 'items' => ['hello', 'world']]],
            ['tag' => 'h1', 'attr' => ['class' => 'header'], 'content' => '<u>Title: $("title")</u>'],
            ['tag' => 'pre', 'content' => 'Content'],
            ['tag' => 'ul', 'children' => [
                ['expression' => 'for($i=0; $i<5; ++$i)', 'children' =>  [
                    ['tag' => 'li', 'content' => '$i'],
                ]],
                ['expression' => 'foreach($items as $i)', 'children' =>  [
                    ['tag' => 'li', 'content' => 'Item: @($i,"<b>test</b>")'],
                ]],
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
        $data = is_array($source) ? $source : json_decode($source, true);
        $html = new \hikari\html\Html;
        foreach($data as $key => $value) {
            $type = isset($value['type']) ? $value['type'] : null;
            if($type == 'data') {
                $result[] = '<?php';
                foreach($value['value'] as $k => $v) {
                    $result[] = sprintf('${%s} = %s;', var_export($k, true), var_export($v, true));
                }
                $result[] = '?>';
                continue;
            }
            $tag = isset($value['tag']) ? $value['tag'] : null;
            $attr = isset($value['attr']) ? $value['attr'] : [];
            $content = isset($value['content']) ? $value['content'] : null;
            if(isset($value['expression'])) {
                $result[] = sprintf('<?php %s { ?>', $value['expression']);
            } else if($tag) {
                $result[] = $html->open($tag, $attr);
            }
            if(isset($value['children'])) {
                $result[] = $this->compile($value['children']);
            } else if($content !== null) {
                $result[] = $this->interpolate($content);
            }
            if(isset($value['expression'])) {
                $result[] = '<?php } ?>';
            } else if($tag) {
                $result[] = $html->close($tag);
            }
        }
        return implode(PHP_EOL, $result);
    }

    function interpolate($string) {
        $string = str_replace(['@(', '$(', ')'], ['<?php echo $this->escape(', '<?php echo $this->get(', ')?>'], $string);
        return $string;
    }

}