<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: ../authentication/login.php");
    exit();
}

include("../config/database.php");

$surveys = mysqli_query($conn,
"SELECT * FROM surveys ORDER BY survey_id DESC");
?>

<!DOCTYPE html>
<html>

<head>

<title>Survey Results</title>

<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<div class="container">

<h1>Survey Results</h1>

<p>Select a survey to view its results.</p>

<table>

<tr>

<th>ID</th>

<th>Survey</th>

<th>Status</th>

<th>Action</th>

</tr>

<?php while($row=mysqli_fetch_assoc($surveys)){ ?>

<tr>

<td><?php echo $row['survey_id']; ?></td>

<td><?php echo htmlspecialchars($row['title']); ?></td>

<td><?php echo $row['status']; ?></td>

<td>

<a
class="btn btn-success"
href="view_results.php?survey_id=<?php echo $row['survey_id']; ?>">

Results

</a>

<a
class="btn"
href="respondents.php?survey_id=<?php echo $row['survey_id']; ?>">

Respondents

</a>

</td>

</tr>

<?php } ?>

</table>

<br>

<a
class="btn"
href="dashboard.php">

← Dashboard

</a>

</div>

</body>

</html>