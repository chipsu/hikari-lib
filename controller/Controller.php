<?php

namespace hikari\controller;

class Controller extends ControllerAbstract {

    function mixins() {
        $mixins = parent::mixins();
        $mixins = array_merge($this->coreMixins(), $mixins);
        return $mixins;
    }

    function coreMixins() {
        return [
            'responseFilter' => [
                'class' => '\hikari\filter\Response',
                'formats' => [
                    'application/json' => '\hikari\renderer\Json',
                    'application/xml' => '\hikari\renderer\Xml',
                    'text/html' => '\hikari\renderer\View',
                    '*' => '\hikari\renderer\Text',
                ],
            ],
        ];
    }
}