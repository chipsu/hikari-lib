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
            if(!$result) {
                if($throw) {
                    \hikari\exception\Http::raise($throw);
                }
                return null;
            }
        } else {
            $result = $class::find($query, ['hydrator' => true]);
            $result->skip = (int)$this->request->header('X-Skip', $this->request->query('skip', 0));
            $result->limit = (int)$this->request->header('X-Limit', $this->request->query('limit', 20));
            if($sortString = $this->getSort()) {
                $result->sort = $this->parseSortString($sortString);
            }
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

    protected function dataCount() {
        $class = $this->modelClassName();
        $query = $this->buildRequestQuery();
        return $class::count($query);
    }

    protected function getSkip() {
        return (int)$this->request->query('skip', 0);
    }

    protected function getLimit() {
        // TODO: default limit
        return (int)$this->request->query('limit', 20);
    }

    protected function getSort() {
        return $this->request->query('sort', false);
    }

    protected function parseSortString($sortString) {
        // TODO: $model->getSortFields..
        $result = [];
        $parts = explode(',', $sortString);
        foreach($parts as $part) {
            if(!preg_match('/^(?<field>[_\.a-z]+)\:(?<order>asc|desc|-1|1)$/', $part, $match)) {
                \hikari\exception\Http::raise(500, __METHOD__);
            }
            $match['order'] = (int)str_replace(['asc', 'desc'], [1, -1], $match['order']);
            $result[$match['field']] = $match['order'];
        }
        return $result;
    }
}
