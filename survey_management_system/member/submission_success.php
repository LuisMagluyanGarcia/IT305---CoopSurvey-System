<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../authentication/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Survey Submitted</title>

<link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="container">

<div class="success-box">

<h1>✅ Survey Submitted Successfully!</h1>

<p>

Thank you for participating in this survey.

Your response has been recorded successfully.

</p>

<br>

<a
class="btn btn-success"
href="dashboard.php">

Return to Dashboard

</a>

<a
class="btn"
href="surveys.php">

Available Surveys

</a>

</div>

</div>

</body>

</html>