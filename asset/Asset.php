<?php

namespace hikari\asset;

use \hikari\component\Component;
use \hikari\system\Shell;
use \hikari\exception\Exception as CompilerException;

/**
 *
 * @todo Persistent cache
 * @todo filemtime check
 */
class Asset extends Component {
    public $compiled = [];
    public $compilers = [
        'sass' => ['method' => 'compileSass', 'output' => 'css'],
        'less' => ['method' => 'compileLess', 'output' => 'css'],
        'css' => ['method' => 'minify'],
        'typescript' => ['method' => 'compileTypeScript', 'output' => 'js'],
        'coffee' => ['method' => 'compileCoffeeScript', 'output' => 'js'],
        'js' => ['method' => 'minify'],
        'image' => ['method' => 'optimizeImage', 'extensions' => ['jpg', 'jpeg', 'png', 'gif']],
    ];

    function url($asset, array $options = []) {
        if(!isset($this->compiled[$asset])) {
            $this->compiled[$asset] = $this->publish($asset);
        }
        return $this->compiled[$asset];
    }

    function publish($asset, array $options = []) {
        $path = isset($options['path']) ? $options['path'] : 'asset';
        if(strpos($asset, '://') !== false) {
            $src = $asset;
            // TODO: Fetch URL
            return $src;
        } else {
            $src = $this->application->path . '/asset/' . $asset;
            is_file($src) or \hikari\exception\NotFound::raise($src);
        }
        $dst = $this->application->publicPath . '/' . $path;
        $info = pathinfo($src);
        $name = sha1($src);
        $type = isset($options['type']) ? $options['type'] : $info['extension'];
        if(isset($this->compilers[$type])) {
            $compiler = $this->compilers[$type];
        } else {
            $compiler = ['method' => 'minify'];
        }
        $method = is_array($compiler['method']) || $compiler['method'] instanceof \Closeure
                ? $compiler['method']
                : array($this, $compiler['method']);
        $name .= '.';
        $name .= isset($compiler['output']) ? $compiler['output'] : $info['extension'];
        $dst .= '/' . $name;
        call_user_func($method, $type, $src, $dst);
        return '/' . $path . '/' . $name;
    }

    function minify($type, $src, $dst, array $options = []) {
        copy($src, $dst);
    }

    function compileSass($type, $src, $dst, array $options = []) {
        $shell = new Shell;
        $shell->run('sass', ['-t', 'compressed', $src, $dst]) or CompilerException::raise($shell);
    }

    function compileLess($type, $src, $dst, array $options = []) {
        copy($src, $dst);
    }

    function compileImage($type, $src, $dst, array $options = []) {
        // Resize, crop and optimize
        copy($src, $dst);
    }
}