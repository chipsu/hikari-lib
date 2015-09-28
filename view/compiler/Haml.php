<?php

namespace hikari\view\compiler;

# TODO: use composer (optional dependency)
require_once __DIR__ . '/../../../haml-php/src/HamlPHP/HamlPHP.php';
require_once __DIR__ . '/../../../haml-php/src/HamlPHP/Storage/FileStorage.php';

class Haml extends CompilerAbstract {
    function source($source, array $options = []) {
        \HamlPHP::$Config['escape_html_default'] = true;
        $haml = new \HamlPHP;
        $haml->disableCache();
        $result = $haml->getCompiler()->parseString($source);
        $include = '<?php '.
            'require_once \'' . __DIR__ . '/../../../haml-php/src/HamlPHP/HamlPHP.php\';'.
            'require_once \'' . __DIR__ . '/../../../haml-php/src/HamlPHP/Storage/FileStorage.php\';'.
            ' ?>';
        return $include . $result;
    }
}
