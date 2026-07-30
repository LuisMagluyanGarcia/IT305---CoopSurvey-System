<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != "Member") {
    header("Location: ../authentication/login.php");
    exit();
}

include("../config/database.php");

$memberID = $_SESSION['user_id'];

$totalSurvey = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total
FROM surveys
WHERE status='Active'"));

$totalAnswered = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total
FROM responses
WHERE member_id='$memberID'"));
?>

<!DOCTYPE html>
<html>

<head>

<title>Member Dashboard</title>

<link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="container">

<h1>Member Dashboard</h1>

<hr>

<h2>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></h2>

<div class="card">

<h3>Available Surveys</h3>

<h1><?php echo $totalSurvey['total']; ?></h1>

</div>

<br>

<div class="card">

<h3>Completed Surveys</h3>

<h1><?php echo $totalAnswered['total']; ?></h1>

</div>

<br>

<a class="btn btn-success"
href="surveys.php">

Take Survey

</a>

<br><br>

<a class="btn"
href="profile.php">

My Profile

</a>

<br><br>

<a
class="btn"
href="../authentication/change_password.php">

Change Password

</a>

<br><br>

<a class="btn btn-delete"
href="../authentication/logout.php">

Logout

</a>

</div>

</body>

</html>