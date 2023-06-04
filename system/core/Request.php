<?php

namespace System\Core;

class Request {

    public array $path;

    public function __construct() {
        $this->setConstants();
        $url = $_SERVER["REQUEST_URI"];
        $uri = $this->parseURI($url);
        $this->path = $uri;
    }

    public function is(string $type, bool $checkFile = false) {
        $file = BASE_PATH . $_SERVER["REQUEST_URI"];
        return $this->path[0] === $type && ($checkFile ? file_exists($file) && is_file($file) : 1);
    }

    private function setConstants() {
        define('BASE_PATH', $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR);
        set_include_path(BASE_PATH);
    }

    private function parseURI(string $url) {
        $uri = explode('/', $url);
        array_shift($uri);
        return $uri;
    }

}
