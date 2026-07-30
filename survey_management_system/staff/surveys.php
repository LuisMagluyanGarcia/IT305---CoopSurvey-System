<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: ../authentication/login.php");
    exit();
}

include("../config/database.php");

$sql = "SELECT * FROM surveys ORDER BY survey_id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>

<head>

    <title>Survey Management</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="container">

    <h1>Survey Management</h1>

    <a href="dashboard.php" class="btn">← Dashboard</a>

    <a href="add_survey.php" class="btn btn-success">
        + Add Survey
    </a>

    <br><br>

    <table>

        <tr>

            <th>ID</th>

            <th>Title</th>

            <th>Description</th>

            <th>Opening Date</th>

            <th>Closing Date</th>

            <th>Status</th>

            <th>Actions</th>

        </tr>

        <?php while($row = mysqli_fetch_assoc($result)){ ?>

        <tr>

            <td><?php echo $row['survey_id']; ?></td>

            <td><?php echo $row['title']; ?></td>

            <td><?php echo $row['description']; ?></td>

            <td><?php echo $row['opening_date']; ?></td>

            <td><?php echo $row['closing_date']; ?></td>

            <td>

                <?php
                if($row['status']=="Active"){
                    echo "<span class='active'>Active</span>";
                }else{
                    echo "<span class='inactive'>Inactive</span>";
                }
                ?>

            </td>

            <td>

<a class="btn"
href="questions.php?survey_id=<?php echo $row['survey_id']; ?>">
    Questions
</a>

                <a
class="btn btn-delete"
href="../process/delete_survey.php?id=<?php echo $row['survey_id']; ?>"
onclick="return confirm('Are you sure you want to delete this survey?');">
Delete
</a>

            </td>

        </tr>

        <?php } ?>

    </table>

</div>

</body>

</html>