<?php
// Redirect to profile page
session_start();
require_once '../../includes/functions.php';
if(isStudent()) redirect(BASE_PATH.'/modules/student_profiles/edit.php');
if(isAdmin())   redirect(BASE_PATH.'/modules/student_profiles/list.php');
redirect(BASE_PATH.'/auth/login.php');
