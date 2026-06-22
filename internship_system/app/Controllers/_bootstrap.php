<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$_bs_root = dirname(__DIR__, 2) . '/';

require_once $_bs_root . 'config/database.php';
require_once $_bs_root . 'includes/functions.php';
require_once $_bs_root . 'app/Controllers/BaseController.php';

defined('BASE_PATH_FS') || define('BASE_PATH_FS', $_bs_root);

unset($_bs_root);
