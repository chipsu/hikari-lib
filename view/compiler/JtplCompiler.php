<?php

namespace hikari\view\compiler;

abstract class JtplNode {
    public $data;
    public $code;
    public $context;
    public $parent;
    public static $html;
    function __construct(array $data = [], $parent = null, $context = null) {
        $this->context = $context;
        $this->data = $data;
        $this->parent = $parent;
    }
    function add($node) {
        if(empty($this->data['children'])) {
            $this->data['children'] = [];
        }
        $node->parent = $this;
        $node->context = $this->context;
        $this->data['children'][] = $node;
        return $node;
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
    function data() {
        $result = [];
        array_walk_recursive($this->data, function($item) use(&$result) {
            var_dump($item);
            $data = is_array($item) ? $item : $item->data();
            unset($data['parent']);
            $result[] = $data;
        });
        return $result;
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
                if(is_array($data)) {
                    $type = isset($data['type']) ? $data['type'] : 'tag';
                    $class = __NAMESPACE__ . '\Jtpl' . ucfirst($type) . 'Node';
                    $node = new $class($data, $this, $this->context);
                } else {
                    $node = $data;
                }
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
        }
        $this->buildChildren();
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
        $content = $this->get('content');
        $this->push('<?php %s { ?>', $this->context->interpolate($content));
        $this->buildChildren();
        $this->push('<?php } ?>');
    }
}

class JtplApi {
    function cat() {
        return implode('', func_get_args());
    }

    function printf($format) {
        return call_user_func_array('sprintf', func_get_args());
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
        $root = new JtplRootNode(['children' => $data], null, $this);
        return $root->toPhp();
    }

    function interpolate($string) {
        $tags = [];
        $string = preg_replace_callback('/&(?<identifier>\w+)/', function($match) use(&$tags) {
            switch($match['identifier']) {
            case 'each':
                $result = 'foreach';
                break;
            case 'set':
            case 'widget':
                $result = 'if(false) { #';
                break;
            default:
                $result = $match['identifier'];
                break;
            }
            $tags[] = $result;
            return chr(1) . (count($tags) - 1) . chr(2);
        }, $string);
        $string = preg_replace_callback('/@(?<identifier>\w+)/', function($match) use(&$tags) {
            $tags[] = '<?php echo $this->get("' . $match['identifier'] . '", null, true)?>';
            return chr(1) . (count($tags) - 1) . chr(2);
        }, $string);
        $string = preg_replace_callback('/\$(?<identifier>\w+)/', function($match) use(&$tags) {
            #$tags[] = '$this->get("' . $match['identifier'] . '")';
            $tags[] = '${"' . $match['identifier'] . '"}';
            return chr(1) . (count($tags) - 1) . chr(2);
        }, $string);
        /*$string = str_replace(['@(', '$(', ')'], ['<?php echo $this->encode(', '<?php echo $this->get(', ')?>'], $string);*/
        $string = preg_replace_callback('/' . chr(1) . '(?<id>\d+)' . chr(2) . '/', function($match) use($tags) {
            return $tags[$match['id']];
        }, $string);
        return $string;
    }

}
