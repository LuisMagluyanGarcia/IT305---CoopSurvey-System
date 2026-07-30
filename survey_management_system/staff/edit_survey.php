<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: ../authentication/login.php");
    exit();
}

include("../config/database.php");

if (!isset($_GET['id'])) {
    header("Location: surveys.php");
    exit();
}

$id = $_GET['id'];

$sql = "SELECT * FROM surveys WHERE survey_id='$id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    echo "Survey not found.";
    exit();
}

$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit Survey</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="container">

    <h1>Edit Survey</h1>

    <form action="../process/update_survey.php" method="POST">

        <input
        type="hidden"
        name="survey_id"
        value="<?php echo $row['survey_id']; ?>">

        <label>Survey Title</label>

        <input
        type="text"
        name="title"
        value="<?php echo htmlspecialchars($row['title']); ?>"
        required>

        <label>Description</label>

        <textarea
        name="description"
        rows="5"
        required><?php echo htmlspecialchars($row['description']); ?></textarea>

        <label>Opening Date</label>

        <input
        type="date"
        name="opening_date"
        value="<?php echo $row['opening_date']; ?>"
        required>

        <label>Closing Date</label>

        <input
        type="date"
        name="closing_date"
        value="<?php echo $row['closing_date']; ?>"
        required>

        <label>Status</label>

        <select name="status">

            <option value="Active"
            <?php if($row['status']=="Active") echo "selected"; ?>>
                Active
            </option>

            <option value="Inactive"
            <?php if($row['status']=="Inactive") echo "selected"; ?>>
                Inactive
            </option>

        </select>

        <br><br>

        <button type="submit">
            Update Survey
        </button>

        <br><br>

        <a href="surveys.php" class="btn">
            Cancel
        </a>

    </form>

</div>

</body>

</html>