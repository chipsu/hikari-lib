<?php

namespace hikari\view;

interface CompilerInterface {
    function file($fileName, array $options = []);
    function source($source, array $options = []);
}

abstract class CompilerAbstract implements CompilerInterface {
    function file($fileName, array $options = []) {
        $source = file_get_contents($fileName);
        return $this->source($source, $options);
    }
}

class HtplCompiler extends CompilerAbstract {
    function source($source, array $options = []) {
        return [
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
        ];
    }

}

abstract class JtplNode {
    public $node;
    public $code;
    function __construct(array $node) {
        $this->node = $node;
    }
    function code() {
        if($this->code === null) {
            $this->code = [];
            $this->build();
        }
        return $this->code;
    }
    function toPhp() {
        return implode(PHP_EOL, $this->code());
    }
    function phpOpen() {
        $this->code[] = '<?php';
    }
    function phpClose() {
        $this->code[] = '?>';
    }
    function push($code) {
        if(func_num_args() > 1) {
            $code = call_user_func_array('sprintf', func_get_args());
        }
        $this->code[] = $code;
    }
    abstract function build();
}

class JtplDataNode extends JtplNode {
    function build() {
        $this->phpOpen();
        foreach($this->node['value'] as $k => $v) {
            $this->push('${%s} = %s;', var_export($k, true), var_export($v, true));
        }
        $this->phpClose();
    }
}


// foreach($items as $i) =>
// php: same
// js: for(k in $items) { $i = $items[$k]; }
// todo: string concat how?
class JtplCompiler extends CompilerAbstract {
    function source($source, array $options = []) {
        $result = [];
        $data = is_array($source) ? $source : json_decode($source, true);
        $html = new \hikari\html\Html;
        foreach($data as $key => $value) {
            $type = isset($value['type']) ? $value['type'] : null;
            if($type == 'data') {
                $node = new JtplDataNode($value);
                $result += $node->code();
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
                $result[] = $this->source($value['children'], $options);
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
