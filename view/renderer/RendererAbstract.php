<?php

namespace hikari\view\renderer;

use hikari\core\Component;

abstract class RendererAbstract extends Component implements RendererInterface {

    function render($source, array $data, array $options) {
        if(!empty($options['source'])) {
            return file_get_contents($source);
        }
        $buffer = empty($options['direct']);
        if($buffer) {
            ob_start() or \hikari\exception\Core::raise('ob_start failed');
        }
        try {
            $this->renderInternal($source, $data, $options);
        } catch(\Exception $ex) {
            if($buffer) ob_end_clean();
            throw $ex;
        }
        if($buffer) {
            return ob_get_clean();
        }
    }

    abstract protected function renderInternal($_file_, array $_data_, array $_options_);
}