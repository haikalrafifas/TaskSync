<?php

class SetupController {

    public function __construct(private $app) {}

    public function index() {
        $domain = $_POST['domain'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        if ( isset($domain, $username, $password) ) {
            $config = BASE_PATH.'app/default.php';
            $content = file_get_contents($config);
            
            $new = str_replace(['<DOMAIN>', '<USERNAME>', '<PASSWORD>'], [$domain, $username, $password], $content);
            
            file_put_contents($config, $new);
            file_put_contents(BASE_PATH.'system/bin/signature', $this->app->generateUUID());
        }
        return $this->app->view('setup/index', ['title' => APP_NAME . ' - Setup']);
    }

}
