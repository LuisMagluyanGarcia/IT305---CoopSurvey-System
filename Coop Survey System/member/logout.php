<?php
require_once '../config/database.php';
unset($_SESSION['member_id'], $_SESSION['member_name']);
session_destroy();
header('Location: login.php');
exit;
