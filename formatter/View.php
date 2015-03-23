<?php

namespace hikari\formatter;

/**
 * @todo before & afterRender events with controller? IViewSomethignEvent or component properties?
 */
class View extends Formatter {
    public $contentType = 'text/html';

    function run($event) {
        $view = $event->controller->createComponent('view', ['controller' => $event->controller, 'data' => $event->result]);
        $viewFile = $event->controller->getViewFile();
        if($event->controller->beforeRender($event)) {
            $event->result = $view->render($viewFile);
            return $event->controller->afterRender($event);
        }
        return false;
    }
}
