<?php

namespace hikari\view\renderer;

class Php extends RendererAbstract {

    protected function renderInternal($_file_, array $_data_, array $_options_) {
        extract($_data_);
        require($_file_);
    }
}