<?php

namespace hikari\utilities;

use \hikari\component\Component;

/**
 *
 * @todo Persistent cache
 * @todo filemtime check
 */
class Asset extends Component {
    public $compiled = [];
    public $compilers = [
        'sass' => ['method' => 'compileSass', 'extension' => 'css'],
        'less' => ['method' => 'compileLess', 'extension' => 'css'],
        'css' => ['method' => 'minify'],
        'typescript' => ['method' => 'compileTypeScript', 'extension' => 'js'],
        'coffee' => ['method' => 'compileCoffeeScript', 'extension' => 'js'],
        'js' => ['method' => 'minify'],
    ];

    function url($asset) {
        if(!isset($this->compiled[$asset])) {
            $src = $this->application->path . '/asset/' . $asset;
            $this->compiled[$asset] = $this->publish($src);
        }
        return $this->compiled[$asset];
    }

    function publish($src, $path = 'asset') {
        is_file($src) or \hikari\exception\NotFound::raise($src);
        $dst = $this->application->publicPath . '/' . $path;
        $info = pathinfo($src);
        $name = sha1($src);
        $type = $info['extension'];
        if(isset($this->compilers[$type])) {
            $compiler = $this->compilers[$type];
        } else {
            $compiler = ['method' => 'minify'];
        }
        $method = is_array($compiler['method']) || $compiler['method'] instanceof \Closeure
                ? $compiler['method']
                : array($this, $compiler['method']);
        $name .= '.';
        $name .= isset($compiler['extension']) ? $compiler['extension'] : $info['extension'];
        $dst .= '/' . $name;
        call_user_func($method, $type, $src, $dst);
        return '/' . $path . '/' . $name;
    }

    function compileSass($type, $src, $dst) {
        copy($src, $dst);
    }

    function compileLess($type, $src, $dst) {
        copy($src, $dst);
    }

    function minify($type, $src, $dst) {
        copy($src, $dst);
    }
}