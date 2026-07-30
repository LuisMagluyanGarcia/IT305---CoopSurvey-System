<?php

session_start();

include("../config/database.php");

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $title = mysqli_real_escape_string($conn,$_POST['title']);

    $description = mysqli_real_escape_string($conn,$_POST['description']);

    $opening_date = $_POST['opening_date'];

    $closing_date = $_POST['closing_date'];

    $status = $_POST['status'];

    $created_by = $_SESSION['user_id'];

    if($opening_date > $closing_date){

        echo "<script>

        alert('Closing date must be after the opening date.');

        window.history.back();

        </script>";

        exit();

    }

    $sql = "INSERT INTO surveys
    (
        title,
        description,
        opening_date,
        closing_date,
        status,
        created_by
    )

    VALUES
    (
        '$title',
        '$description',
        '$opening_date',
        '$closing_date',
        '$status',
        '$created_by'
    )";

    if(mysqli_query($conn,$sql)){

        header("Location: ../staff/surveys.php");

        exit();

    }else{

        echo "Database Error:<br>";

        echo mysqli_error($conn);

    }

}

?>