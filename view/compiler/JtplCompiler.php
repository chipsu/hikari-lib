<?php

namespace hikari\view\compiler;

abstract class JtplNode {
    public $data;
    public $code;
    public $context;
    public static $html;
    function __construct($context, array $data) {
        $this->context = $context;
        $this->data = $data;
    }
    function get($key, $default = null) {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
    function html() {
        if(!static::$html) {
            static::$html = new \hikari\html\Html;
        }
        return static::$html;
    }
    function code() {
        if($this->code === null) {
            $this->code = [];
            $this->build();
        }
        return $this->code;
    }
    function toPhp() {
        $result = [];
        $code = $this->code();
        array_walk_recursive($code, function($item) use(&$result) {
            $result[] = $item;
        }); 
        return implode(PHP_EOL, $result);
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
    function buildChildren() {
        if(isset($this->data['children'])) {
            foreach($this->data['children'] as $data) {
                $type = isset($data['type']) ? $data['type'] : 'tag';
                $class = __NAMESPACE__ . '\Jtpl' . ucfirst($type) . 'Node';
                $node = new $class($this->context, $data);
                $this->push($node->code());
            }
        }
    }
    abstract function build();
}

class JtplRootNode extends JtplNode {
    function build() {
        $this->buildChildren();
    }
}

class JtplTagNode extends JtplNode {
    function build() {
        $html = $this->html();
        $tag = $this->get('tag');
        $attr = $this->get('attr', []);
        $content = $this->get('content');
        $this->push($html->open($tag, $attr));
        if($content !== null) {
            $content = $this->context->interpolate($content);
            $this->push($content);
        } else {
            $this->buildChildren();
        }
        $this->push($html->close($tag));
    }
}

class JtplDataNode extends JtplNode {
    function build() {
        $this->phpOpen();
        foreach($this->get('value', []) as $k => $v) {
            $this->push('${%s} = %s;', var_export($k, true), var_export($v, true));
        }
        $this->phpClose();
    }
}

class JtplStatementNode extends JtplNode {
    function build() {
        $this->push('<?php %s { ?>', $this->get('statement'));
        $this->buildChildren();
        $this->push('<?php } ?>');
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
        $root = new JtplRootNode($this, ['children' => $data]);
        return $root->toPhp();
    }

    function interpolate($string) {
        $string = str_replace(['@(', '$(', ')'], ['<?php echo $this->encode(', '<?php echo $this->get(', ')?>'], $string);
        return $string;
    }

}
