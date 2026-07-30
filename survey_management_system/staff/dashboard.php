<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: ../authentication/login.php");
    exit();
}

if ($_SESSION['role'] == "Member") {
    header("Location: ../member/dashboard.php");
    exit();
}

include("../config/database.php");

$totalMembers = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM members"));

$totalSurveys = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM surveys"));

$activeSurveys = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM surveys WHERE status='Active'"));

$totalResponses = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM responses"));
?>

<!DOCTYPE html>
<html>

<head>

<title>Staff Dashboard</title>

<link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<h1>Staff Dashboard</h1>

<hr>

<h2>Welcome, <?php echo $_SESSION['fullname']; ?></h2>

<div class="card">

<h3>Total Members</h3>

<h1><?php echo $totalMembers['total']; ?></h1>

</div>

<div class="card">

<h3>Total Surveys</h3>

<h1><?php echo $totalSurveys['total']; ?></h1>

</div>

<div class="card">

<h3>Active Surveys</h3>

<h1><?php echo $activeSurveys['total']; ?></h1>

</div>

<div class="card">

<h3>Total Responses</h3>

<h1><?php echo $totalResponses['total']; ?></h1>

</div>

<hr>

<h3>Menu</h3>

<ul>

<li><a href="surveys.php">Survey Management</a></li>

<li><a href="questions.php">Question Management</a></li>

<li><a href="members.php">Member Management</a></li>

<li><a href="results.php">Survey Results</a></li>

<li><a href="reports.php">Reports</a></li>

<li><a href="../authentication/logout.php">Logout</a></li>

</ul>

</body>
</html>