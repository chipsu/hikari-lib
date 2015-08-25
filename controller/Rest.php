<?php

namespace hikari\controller;

class Rest extends Model implements RestInterface {

    function head() {
        $model = $this->loadModel();
        $this->response->code(204);
        return null;
    }

    function get() {
        $model = $this->loadModel();
        return $model;
    }

    function put() {
        $model = $this->loadModel();
        $data = $this->request->getBodyParams();
        if(!$this->updateModel($model, $data)) {
            \hikari\exception\Http::raise(500, __METHOD__);
        }
        return $model;
    }

    function post() {
        $data = $this->request->getBodyParams();
        $model = $this->createModel($data);
        if(!$model) {
            \hikari\exception\Http::raise(500, __METHOD__);
        }
        return $model;
    }

    function patch() {
        \hikari\exception\NotImplemented::raise(__METHOD__);
    }

    function delete() {
        $model = $this->loadModel();
        $model->delete();
        $this->response->code(204);
        return null;
    }

    function options() {
        \hikari\exception\NotImplemented::raise(__METHOD__);
    }

    protected function getSkip() {
        return (int)$this->request->header('X-Skip', parent::getSkip());
    }

    protected function getLimit() {
        return (int)$this->request->header('X-Limit', parent::getLimit());
    }

    protected function getSort() {
        return $this->request->header('X-Sort', parent::getSort());
    }

    protected function afterAction($event) {
        if($event->result) {
            if($event->result instanceof \Iterator) {
                if(true) {
                    // TODO: Fix this
                    $this->response->header('X-Skip', $event->result->skip);
                    $this->response->header('X-Limit', $event->result->limit);
                    $this->response->header('X-Count', $this->dataCount());
                    if($sortString = $this->getSort()) {
                        $this->response->header('X-Sort', $sortString);
                    }
                }
                $result = [];
                foreach($event->result as $model) {
                    $result[] = $model->toArray();
                }
                $event->result = $result;
            } else {
                $event->result = $event->result->toArray();
            }
        }
        return parent::afterAction($event);
    }
}
