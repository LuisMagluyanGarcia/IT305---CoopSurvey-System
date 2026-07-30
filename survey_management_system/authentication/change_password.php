<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Change Password</title>

<link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="container">

<h1>Change Password</h1>

<form action="../process/change_password_process.php" method="POST">

<label>Current Password</label>

<input
type="password"
name="current_password"
required>

<label>New Password</label>

<input
type="password"
name="new_password"
required>

<label>Confirm New Password</label>

<input
type="password"
name="confirm_password"
required>

<br><br>

<button
class="btn btn-success"
type="submit">

Change Password

</button>

<a
class="btn"
href="../member/dashboard.php">

Cancel

</a>

</form>

</div>

</body>

</html>