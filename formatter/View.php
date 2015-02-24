<?php

namespace hikari\formatter;

class View extends Formatter {
    public $contentType = 'text/html';

    function run($event) {
        $view = $event->controller->createComponent('view');
        $viewFile = $event->controller->id . '/' . $event->action->id;
        $view->render($viewFile, $event->result);
        return true;
    }
}
