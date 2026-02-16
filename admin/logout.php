<?php
session_start();
unset($_SESSION['admin_id']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_role']);
session_destroy();

header('Location: ' . (defined('APP_URL') ? APP_URL . '/admin/login.php' : 'http://localhost/Civic-voice/admin/login.php'));
exit();
?>
