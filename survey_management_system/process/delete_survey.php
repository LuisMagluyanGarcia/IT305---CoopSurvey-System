<?php

session_start();

if (!isset($_SESSION['role'])) {
    header("Location: ../authentication/login.php");
    exit();
}

include("../config/database.php");

if (isset($_GET['id'])) {

    $id = intval($_GET['id']);

    $sql = "DELETE FROM surveys WHERE survey_id='$id'";

    if (mysqli_query($conn, $sql)) {

        header("Location: ../staff/surveys.php");
        exit();

    } else {

        echo "Error deleting survey:<br>";
        echo mysqli_error($conn);

    }

} else {

    header("Location: ../staff/surveys.php");
    exit();

}
?>