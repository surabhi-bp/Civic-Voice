<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/Auth.php';

Auth::logout();
header('Location: ' . APP_URL . '/public/index.php');
exit();
?>
