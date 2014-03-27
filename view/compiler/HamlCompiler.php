<?php

namespace hikari\view\compiler;

require_once __DIR__ . '/../../../haml-php/src/HamlPHP/HamlPHP.php';
require_once __DIR__ . '/../../../haml-php/src/HamlPHP/Storage/FileStorage.php';

class HamlCompiler extends CompilerAbstract {
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
