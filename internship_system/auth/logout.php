<?php
require_once '../app/Controllers/_bootstrap.php';
require_once '../app/Controllers/AuthController.php';
(new AuthController($conn))->logout();
