<?php
session_start();

include("../config/database.php");

$result = mysqli_query($conn,
"SELECT * FROM members");
?>

<!DOCTYPE html>

<html>

<head>

<title>Members</title>

<link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<h1>Member Management</h1>

<hr>

<table border="1">

<tr>

<th>ID</th>

<th>Account Number</th>

<th>Name</th>

<th>Email</th>

<th>Status</th>

</tr>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['member_id']; ?></td>

<td><?php echo $row['account_number']; ?></td>

<td><?php echo $row['fullname']; ?></td>

<td><?php echo $row['email']; ?></td>

<td><?php echo $row['status']; ?></td>

</tr>

<?php } ?>

</table>

<br>

<a href="dashboard.php">

Back

</a>

</body>

</html>