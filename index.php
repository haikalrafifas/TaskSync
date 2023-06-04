<?php

use System\Core\Request;
use System\Core\Proxy;
use System\Core\Kernel;

/**
 * Load system files.
 */
$system = __DIR__ . '/system/autoload.php';
require file_exists($system) ? $system : exit('<pre>Failed to load system files!</pre>');

/**
 * Request middleware.
 */
$request = new Request;
if ( $request->is('assets', true) ) return false;
if ( $request->is('proxy') ) return new Proxy($request->path);

/**
 * Bootstrap.
 */
new Kernel($request->path);
