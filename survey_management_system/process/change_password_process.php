<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../authentication/login.php");
    exit();
}

include("../config/database.php");

$currentPassword = $_POST['current_password'];
$newPassword = $_POST['new_password'];
$confirmPassword = $_POST['confirm_password'];

$userID = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($newPassword != $confirmPassword) {

    echo "<script>
    alert('New passwords do not match.');
    window.history.back();
    </script>";

    exit();
}

if ($role == "Member") {

    $table = "members";
    $idField = "member_id";

} else {

    $table = "staff_admin";
    $idField = "staff_id";

}

$sql = "SELECT * FROM $table WHERE $idField='$userID'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if (!$user) {

    die("User not found.");

}

if ($user['password'] != $currentPassword) {

    echo "<script>
    alert('Current password is incorrect.');
    window.history.back();
    </script>";

    exit();
}

$update = "UPDATE $table
SET password='$newPassword'
WHERE $idField='$userID'";

if (mysqli_query($conn, $update)) {

    header("Location: ../authentication/change_password_success.php");

} else {

    echo "Database Error: " . mysqli_error($conn);

}
?>