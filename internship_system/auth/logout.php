<?php
session_start();
session_unset();
session_destroy();
header('Location: /internship-management-system/internship_system/auth/login.php');
exit();
