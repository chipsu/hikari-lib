<?php

namespace hikari\view\compiler;

require_once 'JtplCompiler.php';

class Ptpl implements \ArrayAccess {
    public $_root;
    public $_node;

    function __construct() {
        $this->_root = new JtplRootNode([], null, $this);
        $this->_node = &$this->_root;
    }

    function doctype($type) {
        #$this->_emit->write('<!DOCTYPE HTML>');
        return $this;
    }

    function end() {
        if($this->_node->parent) {
            var_dump("pop<br>");
            $this->_node = $this->_node->parent;
        } else {
            die('xxx');
            \hikari\exception\Argument::raise('Already at root: ' . print_r($this->_node->data, true));
        }
        return $this;
    }

    function _push($tag, array $args) {
        $attr = [];
        $content = null;
        foreach($args as $arg) {
            if(is_array($arg)) {
                $attr = $arg;
            } else {
                $content = $arg;
            }
        }
        $node = new JtplTagNode(['tag' => $tag, 'attr' => $attr, 'content' => $content]);
        if($content === null) {
            $this->_node->add($node);
        } else {
            $this->_node = $this->_node->add($node);                
        }
    }

    function __call($method, array $args) {
        if(method_exists($this, '_' . $method)) {
            return call_user_func_array(array($this, '_' . $method), $args);
        } else {
            $this->_push($method, $args);
        }
        return $this;
    }

    #function __get($key) {
    #    echo('__get');
    #    var_dump($key);
    #    $this->_stack[] = $key;
    #    return $this;
    #}

    #ublic function __callStatic($method, array $args) {
    #    return static::$_instance;
    #}

    #function __invoke(array $args) {
    #    echo('__invoke');
    #    var_dump($args);
    #    return $this;
    #}

    function offsetSet($offset, $value) {
        throw new \Exception(__METHOD__ . ': ' . var_export($offset, true) . ', ' . var_export($value, true));
    }
    
    function offsetExists($offset) {
        return true;
    }
    
    function offsetUnset($offset) {
        #unset($this->container[$offset]);
    }

    function offsetGet($offset) {
        echo 'offsetGet';
        $this->_node = $this->_node->parent;
        return $this;
    }

    function _if($condition) {
        echo "IF[";
        var_dump($condition);
        echo "]";
        return $this;
    }

    function each($items, &$item) {
        $item = '$item';
        return $this;
    }
}

class PtplDataSource {
    function __get($key) {
        return '$' . $key;
    }

    function __toString() {

    }
}

class PtplCompiler extends CompilerAbstract {
    function file($fileName, array $options = []) {
        $tpl = new Ptpl;
        $var = new PtplDataSource;
        require($fileName);
        $result = $tpl->_root->data();
        var_dump($result);
        die;
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
    function source($source, array $options = []) {
        $temp = sys_get_temp_dir() . '/' . uniqid();
        file_put_contents($temp, $source);
        $result = $this->file($temp, $options);
        unlink($temp);
        return $result;
    }
}

