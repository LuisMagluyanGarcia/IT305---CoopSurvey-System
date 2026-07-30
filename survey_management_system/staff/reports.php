<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: ../authentication/login.php");
    exit();
}

include("../config/database.php");

$totalSurveys = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM surveys"));

$totalMembers = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM members"));

$totalResponses = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM responses"));

$activeSurveys = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM surveys WHERE status='Active'"));

$inactiveSurveys = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM surveys WHERE status='Inactive'"));
?>

<!DOCTYPE html>
<html>

<head>

<title>Reports</title>

<link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="container">

<h1>System Reports</h1>

<div class="dashboard-grid">

<div class="card">
<h3>Total Surveys</h3>
<h1><?php echo $totalSurveys['total']; ?></h1>
</div>

<div class="card">
<h3>Total Members</h3>
<h1><?php echo $totalMembers['total']; ?></h1>
</div>

<div class="card">
<h3>Total Responses</h3>
<h1><?php echo $totalResponses['total']; ?></h1>
</div>

<div class="card">
<h3>Active Surveys</h3>
<h1><?php echo $activeSurveys['total']; ?></h1>
</div>

<div class="card">
<h3>Inactive Surveys</h3>
<h1><?php echo $inactiveSurveys['total']; ?></h1>
</div>

</div>

<br>

<a class="btn" href="dashboard.php">

← Back to Dashboard

</a>

</div>

</body>

</html>