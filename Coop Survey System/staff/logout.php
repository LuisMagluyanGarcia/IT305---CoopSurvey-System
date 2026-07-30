<?php
require_once '../config/database.php';
unset($_SESSION['staff_id'], $_SESSION['staff_name'], $_SESSION['staff_role']);
session_destroy();
header('Location: login.php');
exit;
