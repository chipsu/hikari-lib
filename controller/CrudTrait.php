<?php

namespace hikari\controller;

use \hikari\core\Server;

trait CrudTrait {
    use ModelTrait;

    // if numeric array: batch create
    function create() {
        $model = null;
        $class = $this->modelClassName();
        if($data = $this->request->post('data')) {
            $model = $class::create($data);
            if($model->validate()) {
                $model->save();
                header('Location: ' . Server::referer());
                die;
            }
        }
        $this->viewFile = 'post/edit';
        return ['title' => __METHOD__, 'model' => $model ? $model : $class::create()];
    }

    function read() {
        $class = $this->modelClassName();
        $query = $this->requestQuery();
        #if(!empty($query['_id'])) {
        #    $result = $class::one($query, ['hydrator' => true]);
        #    if(!$result) {
        #        \hikari\exception\Http::raise(404);
        #    }
        #} else {
        #    $result = $class::find($query, ['hydrator' => true]);
        #}
        #    $result = $class::one($query, ['hydrator' => true]);
        $result = $class::find($query, ['hydrator' => true]);
        if(!$result && !empty($query['_id'])) {
            \hikari\exception\Http::raise(404);
        }
        return ['title' => __METHOD__, 'result' => $result];
    }

    // if numeric array: batch update
    function update() {
        $class = $this->modelClassName();
        $query = $this->requestQuery();
        $model = $class::one($query, ['hydrator' => true]);
        if(!$model) {
            \hikari\exception\Http::raise(404);
        }
        if($data = $this->request->post('data')) {
            $model->attributes($this->request->data);
            if($model->validate()) {
                $model->save();
                header('Location: ' . Server::referer());
                die;
            }
        }
        $this->viewFile = 'post/edit';
        return ['title' => __METHOD__, 'model' => $model];
    }

    // if numeric array: batch delete
    function dispose() {
        $class = $this->modelClassName();
        $model = $class::one($this->request->get('id'), ['hydrator' => true]);
        if(!$model) {
            \hikari\exception\Http::raise(404);
        }
        $model->delete();
        header('Location: ' . Server::referer());
        die;
    }

    protected function requestQuery() {
        $query = array(
            '_id' => $this->request->get('id'),
            //'data.type' => $this->request->get('type'),
        );
        return array_filter($query, function($item) { return $item !== null; });
    }
}
