<?php
require_once '../app/Controllers/_bootstrap.php';
require_once '../app/Controllers/DashboardController.php';
(new DashboardController($conn))->lecturer();
