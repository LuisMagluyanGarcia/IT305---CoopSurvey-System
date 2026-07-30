<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: ../authentication/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Add Survey</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="container">

    <h1>Add Survey</h1>

    <form action="../process/survey_process.php" method="POST">

        <label>Survey Title</label>
        <input type="text" name="title" required>

        <label>Description</label>
        <textarea name="description" required></textarea>

        <label>Opening Date</label>
        <input type="date" name="opening_date" required>

        <label>Closing Date</label>
        <input type="date" name="closing_date" required>

        <label>Status</label>

        <select name="status">

            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>

        </select>

        <br><br>

        <button type="submit">Save Survey</button>

        <br><br>

        <a href="surveys.php" class="btn">Back</a>

    </form>

</div>

</body>

</html>