<?php

namespace hikari\controller;

class Model extends Controller implements ModelInterface {

    static function modelClassName() {
        return str_replace('\\controller\\', '\\model\\', get_called_class());
    }

    protected function buildRequestQuery() {
        $query = array(
            '_id' => $this->request->query('id'),
        );
        return array_filter($query, function($item) { return $item !== null; });
    }

    protected function loadModel($throw = 404) {
        $class = $this->modelClassName();
        $query = $this->buildRequestQuery();
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
        if(!$result && isset($query['_id'])) {
            if($throw) {
                \hikari\exception\Http::raise($throw);
            }
            return null;
        }
        return $result;
    }

    protected function createModel($data) {
        $class = $this->modelClassName();
        $model = $class::create($data);
        if($model->validate() && $model->save()) {
            return $model;
        }
        return null;
    }

    protected function updateModel($model, $data) {
        $class = $this->modelClassName();
        $query = $this->buildRequestQuery();
        $model->attributes($data);
        if($model->validate()) {
            return $model->save();
        }
        return false;
    }
}
