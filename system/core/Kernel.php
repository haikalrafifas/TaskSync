<?php

namespace System\Core;

use System\Core\Metadata;

/**
 * The Kernel class is responsible for handling the request
 * and routing it to the appropriate controller and method.
 */
class Kernel {

    /**
     * The default controller to use.
     * 
     * @var string
     */
    private string $controller;

    /**
     * The default method to use.
     * 
     * @var string
     */
    public string $method;

    /**
     * The parameters from the URI.
     * 
     * @var array
     */
    public array $params = [];

    /**
     * The data to be passed to the view.
     * 
     * @var array
     */
    private array $data = [];

    /**
     * The default values from .env file.
     *
     * @var array
     */
    public array $default = [];

    /**
     * App access token if user is authenticated
     *
     * @var string
     */
    public string $accessToken;

    public array $metadata;

    /**
     * The constructor for the App class.
     * 
     * @param object $uri The requested URL.
     */
    public function __construct(public array $uri) {
        $metadata = new Metadata('package.json');
        $this->default = $metadata->content['default'];
        unset($metadata->content['default']);
        $this->metadata = $metadata->content;
        
        $this->setDefaultValues();
        $this->startSession($this->default['cookiename']);

        return $this->controller($this->controller);
    }

    /**
     * Load the specified controller.
     * 
     * @param string $name The name of the controller to load.
     * @return void
     */
    public function controller(string $name) {
        $controllerName = ucfirst($name)."Controller";
        $file = "{$this->default["pathController"]}$controllerName.php";
    
        $this->checkFileExistence($file, 'Controller');
        $this->checkAuthentication();
        $this->setDefaultTitle();
    
        include $file;
        $controller = new $controllerName($this);
    
        $method = $this->method;
        method_exists($controller, $method)? :exit("<pre>Method does not exist!</pre>");

        call_user_func_array([$controller, $method], $this->params);
    }

    /**
     * Load the specified view.
     * 
     * @param string $name The name of the view file to be rendered.
     * @param array $data Optional associative array of data to be passed to the view.
     * @return void
     */
    public function view(string $name, array $data = []) {
        $data = array_merge($this->data, $data);
        extract($data);
        $view = $this->default["pathView"];
    
        $file = "$view$name.php";
        $this->checkFileExistence($file, 'View', true);
        
        foreach (["{templates}/header", $name, "{templates}/footer"] as $_) include "$view$_.php";
    }

    /*
    ===============================================================================================
                                        HELPER FUNCTIONS
    ===============================================================================================
    */

    /**
     * Allow URL parameters to be passed to the controller.
     * Default is disabled for security reasons.
     *
     * @return void
     */
    public function setAllowParams() {
        return $this->params = array_slice($this->data["URI"], 2);
    }

    private function setDefaultTitle() {
        return $this->data["title"] = APP_NAME;/* . ( $this->method === $this->default['method'] ? '' : ' - ' . ucwords(str_replace('_', ' ', $this->method)) );*/
    }

    private function setDefaultValues() {
        define('APP_NAME', $this->metadata["name"]??'');
        define('ROOT', $this->metadata['uri']['root']??'');
        
        $this->default["pathController"] = "app/controllers/";
        $this->default["pathModel"] = "app/models/";
        $this->default["pathView"] = "app/views/";
        
        foreach ( $this->default as $default ) {
            $this->default[] = $default;
        }
        
        $this->data["URI"] = $this->uri;
        $this->data["api"] = $this->default["api"] ?? '';
        $this->controller  = $this->uri[0] ?: $this->default["controller"];
        $this->method      = $this->uri[1] ?? $this->default["method"];
    }

    public function checkFileExistence(string $file, string $type, bool $checkType = false) {
        return file_exists(BASE_PATH.$file) && ( $checkType? is_file(BASE_PATH.$file):1 )? :exit("<pre>ERROR: $type file does not exist!</pre>");
    }

    private function checkAuthentication() {
        $defaultController = $this->default["controller"];
        $isAuth = $this->data["isAuth"];

        if ( $isAuth && $this->controller === $defaultController ) { header("Location: ".ROOT.$this->default['uri']['app']); }
        if ( !$isAuth && $this->controller !== $defaultController ) { header("Location: ".ROOT.$this->default['uri']['auth']); }
    }

    private function startSession(string $sessionName) {
        session_name($sessionName);
        session_start();
        $this->accessToken = $_SESSION["KEY"] ?? '';
        $this->data["isAuth"] = !empty($this->accessToken);
    }

    public function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
}
