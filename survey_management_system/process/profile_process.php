<?php

include("../config/database.php");

$id = $_POST['member_id'];

$email = $_POST['email'];

$phone = $_POST['phone'];

$address = $_POST['address'];

$sql = "UPDATE members

SET

email='$email',

phone='$phone',

address='$address'

WHERE member_id='$id'";

mysqli_query($conn,$sql);

header("Location: ../member/profile.php");

exit();

?>