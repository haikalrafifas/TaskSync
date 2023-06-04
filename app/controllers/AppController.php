<?php

class AppController {

    public function __construct(private $app) {}

    public function index() {
        return $this->app->view('app/index', ['title' => APP_NAME]);
    }

}
