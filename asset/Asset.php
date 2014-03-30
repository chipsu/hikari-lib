<?php

namespace hikari\asset;

use \hikari\component\Component;
use \hikari\system\Shell;
use \hikari\exception\Exception;
use \hikari\exception\Exception as CompilerException;
use \hikari\exception\NotSupported;
use \hikari\exception\NotFound;

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
        'image' => ['method' => 'compileImage', 'extensions' => ['jpg', 'jpeg', 'png', 'gif']],
        'json' => ['method' => 'compileJson', 'output' => 'dat'],
        'font' => ['method' => 'copyFile', 'extensions' => ['eot', 'woff', 'ttf', 'svg']],
    ];
    public $watch = true;
    public $debug = true;
    public $assetPath;

    function __construct(array $parameters = []) {
        parent::__construct($parameters);
    }

    function initialize() {
        if(empty($this->assetPath)) {
            $this->assetPath = $this->application->path . '/asset';
        }
        parent::initialize();
    }

    function url($asset, array $options = []) {
        $id = $asset . json_encode($options);
        if($this->cache->value($id, $result)) {
            $dst = $this->application->publicPath . '/' . $result;
            if($this->watch && strpos($asset, '://') === false) {
                $src = $this->src($asset, $options);
                if(is_file($dst) && filemtime(dirname($src)) <= filemtime($dst)) {
                    return $result;
                }
            } else if(is_file($dst)) {
                return $result;
            }
        }
        $result = $this->publish($asset, $options);
        $this->cache->set($id, $result);
        return $result;
    }

    function src($asset, array $options = []) {
        if(empty($options['absolute'])) {
            $src = $this->assetPath . '/' . $asset;
        } else {
            $src = $asset;
        }
        return $src;
    }

    function publish($asset, array $options = []) {
        $path = isset($options['path']) ? $options['path'] : 'asset';
        $dst = $this->application->publicPath . '/' . $path;
        if(strpos($asset, '://') !== false) {
            $name = isset($options['name']) ? $options['name'] : 'download-' . sha1($asset);
            $src = $dst . '/' . $name;
            $name = $this->trimExtension($name);
            if(!is_file($src)) {
                \hikari\utilities\File::ensureDirectoryExists(dirname($src));
                touch($src);
                $fp = fopen($src, 'w+');
                $ch = curl_init(str_replace(' ', '%20', $asset));
                curl_setopt($ch, CURLOPT_TIMEOUT, 50);
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);
            }
        } else {
            $src = $this->src($asset, $options);
            is_file($src) or NotFound::raise($src);
            $name = $this->trimExtension(isset($options['name']) ? $options['name'] : $asset);
        }
        $info = pathinfo($src);
        if(isset($options['type'])) {
            $type = $options['type'];
        } else if(isset($info['extension'])) {
            $type = strtolower($info['extension']);
        } else {
            $finfo = new \finfo(\FILEINFO_MIME_TYPE);
            $mimetype = $finfo->file($src);
            list($_, $type) = explode('/', $mimetype);
        }
        if(isset($this->compilers[$type])) {
            $compiler = $this->compilers[$type];
        } else {
            $compiler = ['method' => 'minify'];
            foreach($this->compilers as $c) {
                if(isset($c['extensions']) && in_array($type, $c['extensions'])) {
                    $compiler = $c;
                    break;
                }
            }
        }
        $method = is_array($compiler['method']) || $compiler['method'] instanceof \Closeure
                ? $compiler['method']
                : array($this, $compiler['method']);
        $name .= '.';
        $name .= isset($options['output']) ? $options['output'] : (
            isset($compiler['output']) ? $compiler['output'] : $type
        );
        $dst .= '/' . $name;
        \hikari\utilities\File::ensureDirectoryExists(dirname($dst));
        $result = call_user_func($method, $type, $src, $dst, $options);
        return is_string($result) ? $result : '/' . $path . '/' . $name;
    }

    function copyFile($type, $src, $dst, array $options = []) {
        copy($src, $dst) or CompilerException::raise('Could not copy file "%s" to "%s"', $src, $dst);
    }

    function minify($type, $src, $dst, array $options = []) {
        $shell = new Shell;
        switch($type) {
        case 'js':
            $shell->run('uglifyjs', ['-nc', $src]) or CompilerException::raise($shell);
            file_put_contents($dst, implode(PHP_EOL, $shell->output));
            break;
        default:
            NotSupported::raise($type);
        }
    }

    function compileSass($type, $src, $dst, array $options = []) {
        $shell = new Shell;
        $shell->run('sass', ['-t', 'compressed', '-q', '-C', $src, $dst]) or CompilerException::raise($shell);
    }

    function compileLess($type, $src, $dst, array $options = []) {
        NotSupported::raise($type);
    }

    function compileImage($type, $src, $dst, array $options = []) {
        if(empty($options)) {
            copy($src, $dst);
        } else {
            switch($type) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($src);
                break;
            case 'png':
                $image = imagecreatefrompng($src);
                break;
            case 'gif':
                $image = imagecreatefromgif($src);
                break;
            default:
                \hikari\exception\NotSupported::raise($type);
            }
            foreach($options as $key => $value) {
                switch($key) {
                case 'crop':
                    $image = imagecrop($image, $value);
                    break;
                case 'size':
                    $width = isset($value[0]) ? $value[0] : $value;
                    $height = isset($value[1]) ? $value[1] : -1;
                    $image = imagescale($image, $width, $height);
                    break;
                case 'flip':
                    switch($value) {
                    case 'horizontal':
                        $mode = IMG_FLIP_HORIZONTAL;
                        break;
                    case 'vertical':
                        $mode = IMG_FLIP_VERTICAL;
                        break;
                    case 'both':
                        $mode = IMG_FLIP_BOTH;
                        break;
                    default:
                        NotSupported::raise($value);
                    }
                    imageflip($image, $mode);
                    break;
                case 'rotate':
                    $angle = $value;
                    $color = imagecolorallocatealpha($image, 0, 0, 0, 127);
                    $image = imagerotate($image, $angle, $color);
                    break;
                }
            }
            $output = isset($options['output']) ? $options['output'] : $type;
            $method = 'image' . ($output == 'jpg' ? 'jpeg' : $output);
            call_user_func($method, $image, $dst) or CompilerException::raise('Could not save image');
        }
    }

    function compileCoffeeScript($type, $src, $dst, array $options = []) {
        $shell = new Shell;
        $shell->run('coffee', ['-c', '-o', dirname($dst), '--join', basename($dst), $src]) or CompilerException::raise($shell);
        if(!$this->debug) {
            $this->minify('js', $dst, $dst);
        }
    }

    function compileJson($type, $src, $dst, array $options = []) {
        $path = dirname($src);
        $data = file_get_contents($src);
        $data = json_decode($data, true);
        json_last_error() and CompilerException::raise('JSON decode error in "%s": %s', $src, json_last_error_msg());
        if(isset($data['dependencies'])) {
            foreach($this->expandFileList($data['dependencies'], $path) as $dependency) {
                // TODO: build $dependency
            }
        }
        switch($data['mode']) {
        case 'chain':
            $content = '';
            foreach($this->expandFileList($data['files'], $path) as $file) {
                $content .= file_get_contents($file);
            }
            file_put_contents($dst, $content);
            $name = $this->getAbsoluteAssetName($src);
            $name = $this->trimExtension($name);
            return $this->publish($dst, ['absolute' => true, 'type' => $data['output'], 'name' => $name]);
        case 'compile':
            $src = $this->getAbsoluteAssetName(dirname($src) . '/' . $data['source']);
            return $this->publish($src);
        default:
            NotSupported::raise($data->mode);
        }
    }

    function expandFileList(array $files, $path) {
        $result = [];
        foreach($files as $file) {
            if(strpos($file, '*') === false) {
                $result[] = $path . '/' . $file;
            } else {
                foreach(glob($path . '/' . $file, GLOB_BRACE) as $f) {
                    $result[] = $f;
                }
            }
        }
        return $result;
    }

    function getAbsoluteAssetName($src) {
        return ltrim(substr($src, strlen($this->assetPath)), '/');
    }

    function trimExtension($filename) {
        $info = pathinfo($filename);
        return !empty($info['dirname']) && $info['dirname'] != '.'
            ? $info['dirname'] . '/' . $info['filename']
            : $info['filename'];
    }
}
