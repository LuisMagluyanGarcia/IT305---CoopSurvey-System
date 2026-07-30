<?php

session_start();

include("../config/database.php");

$id = $_SESSION['id'];

$sql = "SELECT *
FROM members
WHERE member_id='$id'";

$row = mysqli_fetch_assoc(mysqli_query($conn,$sql));

?>

<!DOCTYPE html>

<html>

<head>

<title>Profile</title>

<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<h1>My Profile</h1>

<hr>

<form action="../process/profile_process.php"
method="POST">

<input type="hidden"
name="member_id"
value="<?php echo $row['member_id']; ?>">

<label>Full Name</label>

<input
type="text"
value="<?php echo $row['fullname']; ?>"
readonly>

<br><br>

<label>Email</label>

<input
type="email"
name="email"
value="<?php echo $row['email']; ?>">

<br><br>

<label>Phone</label>

<input
type="text"
name="phone"
value="<?php echo $row['phone']; ?>">

<br><br>

<label>Address</label>

<textarea
name="address"><?php echo $row['address']; ?></textarea>

<br><br>

<button>

Update Profile

</button>

</form>

<br>

<a href="dashboard.php">

Back

</a>

</body>

</html>