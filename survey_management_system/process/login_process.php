<?php

session_start();

include("../config/database.php");

$userType = $_POST['user_type'];
$username = $_POST['username'];
$password = $_POST['password'];

if($userType=="staff"){

$sql="SELECT * FROM staff_admin
WHERE username='$username'
AND password='$password'";

$result=mysqli_query($conn,$sql);

if(mysqli_num_rows($result)>0){

$row=mysqli_fetch_assoc($result);

$_SESSION['user_id']=$row['staff_id'];
$_SESSION['fullname']=$row['fullname'];
$_SESSION['role']=$row['role'];

header("Location: ../staff/dashboard.php");

}else{

echo "Invalid Staff Login.";

}

}

else{

$sql="SELECT * FROM members
WHERE account_number='$username'
AND password='$password'";

$result=mysqli_query($conn,$sql);

if(mysqli_num_rows($result)>0){

$row=mysqli_fetch_assoc($result);

$_SESSION['user_id']=$row['member_id'];
$_SESSION['fullname']=$row['fullname'];
$_SESSION['role']="Member";

header("Location: ../member/dashboard.php");

}else{

echo "Invalid Member Login.";

}

}
?>