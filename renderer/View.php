<?php

namespace hikari\renderer;

/**
 * @todo before & afterRender events with controller? IViewSomethignEvent or component properties?
 */
class View extends RendererAbstract {

    function render($event) {
        if($event->controller->beforeRender($event)) {
            $view = $event->controller->createComponent('view', [
                'controller' => $event->controller,
                'data' => $event->result
            ]);
            $viewFile = $event->controller->getViewFile();
            $event->result = $view->render($viewFile);
            return $event->controller->afterRender($event);
        }
        return false;
    }

    public function getContentType() {
        return 'text/html';
    }
}
