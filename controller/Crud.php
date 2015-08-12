<?php

namespace hikari\controller;

use \hikari\core\Server;

class Crud extends Model implements CrudInterface {

    // TODO: Check CSRF-token
    function create() {
        $this->viewFile = 'post/edit';
        if($data = $this->request->post('data')) {
            $model = $this->createModel($data);
            if($model->exists()) {
                $this->response->redirect($this->returnUrl);
            }
        }
        return [
            'model' => $model ? $model : $class::create(),
        ];
    }

    function read() {
        $model = $this->loadModel();
        return [
            'model' => $model,
        ];
    }

    // TODO: Check CSRF-token
    function update() {
        $this->viewFile = 'post/edit';
        $model = $this->loadModel();
        if($data = $this->request->post('data')) {
            if($this->updateModel($model, $data)) {
                $this->response->redirect($this->returnUrl);
            }
        }
        return ['model' => $model];
    }

    // TODO: Check CSRF-token
    function delete() {
        $model = $this->loadModel();
        $model->delete();
        $this->response->redirect($this->returnUrl);
    }

    protected function getReturnUrl() {
        return Server::referer();
    }
}
