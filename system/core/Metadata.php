<?php

namespace System\Core;

class Metadata {

    public array $content;

    public function __construct(string $file) {
        $file = BASE_PATH . $file;
        $this->fileExists($file);
        $data = $this->parse($file);
        return $this->make($data);
    }

    private function parse(string $file) {
        $json = @file_get_contents($file);
        return json_decode($json, true);
    }

    private function make(array $data) {
        $result = [];
        foreach ($data as $key => $value) $result[$key] = $value;
        return $this->content = $result;
    }

    private function fileExists(string $file) {
        return file_exists($file) ?: exit('<pre>Missing metadata file!</pre>');
    }

}
