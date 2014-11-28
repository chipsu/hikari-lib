<?php

namespace hikari\controller;

trait ModelTrait {
    static function modelClassName() {
        return str_replace('\\controller\\', '\\model\\', get_called_class());
    }
}
