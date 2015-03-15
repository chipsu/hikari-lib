<?php

namespace hikari\formatter;

/**
 * @todo before & afterRender events with controller? IViewSomethignEvent or component properties?
 */
class View extends Formatter {
    public $contentType = 'text/html';

    function run($event) {
        $view = $event->controller->createComponent('view', ['controller' => $event->controller, 'data' => $event->result]);
        $viewFile = $event->controller->id . '/' . $event->action->id;
        $event->result = $view->render($viewFile);
        return true;
    }
}
