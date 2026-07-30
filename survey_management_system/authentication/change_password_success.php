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

<title>Password Changed</title>

<link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="container">

<div class="success-box">

<h1>✅ Password Changed Successfully!</h1>

<p>

Your password has been updated.

</p>

<?php
if($_SESSION['role']=="Member"){
?>

<a class="btn btn-success"
href="../member/dashboard.php">

Return to Dashboard

</a>

<?php
}else{
?>

<a class="btn btn-success"
href="../staff/dashboard.php">

Return to Dashboard

</a>

<?php
}
?>

</div>

</div>

</body>

</html>