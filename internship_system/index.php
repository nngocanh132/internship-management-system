<?php
require_once __DIR__ . '/app/Controllers/_bootstrap.php';
require_once __DIR__ . '/app/Core/Router.php';

$r = new Router();
require_once __DIR__ . '/app/Core/routes.php';

$r->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
