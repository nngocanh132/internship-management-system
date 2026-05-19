<?php
// Temporary debug page — delete after use
session_start();
require_once 'includes/functions.php';

echo '<pre>';
echo 'SCRIPT_NAME: ' . $_SERVER['SCRIPT_NAME'] . "\n";
echo 'SCRIPT_FILENAME: ' . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo 'REQUEST_URI: ' . $_SERVER['REQUEST_URI'] . "\n";
echo 'getBaseUrl(): ' . getBaseUrl() . "\n";
echo 'isLoggedIn(): ' . (isLoggedIn() ? 'YES' : 'NO') . "\n";
echo 'SESSION role: ' . ($_SESSION['role'] ?? 'none') . "\n";
echo '</pre>';

echo '<a href="' . getBaseUrl() . '/auth/login.php">→ Login page</a><br>';
echo '<a href="' . getBaseUrl() . '/index.php">→ Dashboard</a>';
