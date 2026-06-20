<?php
session_start();
require_once 'includes/functions.php';
if(isLoggedIn()) redirect(getDashboardUrl());
redirect(BASE_PATH.'/auth/login.php');
