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
        if(!empty($query['_id'])) {
            $result = $class::one($query, ['hydrator' => true]);
            if(!$result && $throw) {
                \hikari\exception\Http::raise($throw);
            }
            return null;
        } else {
            $result = $class::find($query, ['hydrator' => true]);
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
