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
                    'application/json' => '\hikari\formatter\Json',
                    'application/xml' => '\hikari\formatter\Xml',
                    'text/html' => '\hikari\formatter\View',
                    '*' => '\hikari\formatter\Text',
                ],
            ],
        ];
    }
}