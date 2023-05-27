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

    private function setConstants() {
        define('BASE_PATH', $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR);
        set_include_path(BASE_PATH);
    }

    private function parseURI(string $url) {
        $uri = explode('/', $url);
        array_shift($uri);
        return $uri;
    }

    public function is(string $type) {
        $matched = $this->path[0] === $type;
        if ( $type === 'static' ) {
            $static = BASE_PATH . $_SERVER["REQUEST_URI"];
            $matched = $matched && file_exists($static) && is_file($static);
        }
        return $matched;
    }
}
