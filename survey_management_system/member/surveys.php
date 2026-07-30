<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: ../authentication/login.php");
    exit();
}

include("../config/database.php");

$memberID = $_SESSION['user_id'];

$sql = "
SELECT *
FROM surveys
WHERE status='Active'
AND CURDATE() BETWEEN opening_date AND closing_date
ORDER BY survey_id DESC
";

$result = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>

<head>

<title>Available Surveys</title>

<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<div class="container">

<h1>Available Surveys</h1>

<table>

<tr>

<th>Title</th>

<th>Description</th>

<th>Closing Date</th>

<th>Action</th>

</tr>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo htmlspecialchars($row['title']); ?></td>

<td><?php echo htmlspecialchars($row['description']); ?></td>

<td><?php echo $row['closing_date']; ?></td>

<td>

<a
class="btn btn-success"
href="survey_form.php?survey_id=<?php echo $row['survey_id']; ?>">

Take Survey

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