<?php

session_start();

include("../config/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST['survey_id'];

    $title = mysqli_real_escape_string($conn, $_POST['title']);

    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $opening_date = $_POST['opening_date'];

    $closing_date = $_POST['closing_date'];

    $status = $_POST['status'];

    if ($opening_date > $closing_date) {

        echo "<script>
        alert('Closing date must be after the opening date.');
        window.history.back();
        </script>";

        exit();

    }

    $sql = "UPDATE surveys SET

            title='$title',

            description='$description',

            opening_date='$opening_date',

            closing_date='$closing_date',

            status='$status'

            WHERE survey_id='$id'";

    if (mysqli_query($conn, $sql)) {

        header("Location: ../staff/surveys.php");
        exit();

    } else {

        echo "Database Error:<br>";
        echo mysqli_error($conn);

    }

}
?>