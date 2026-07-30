<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: ../authentication/login.php");
    exit();
}

include("../config/database.php");

// =========================
// NO SURVEY SELECTED
// =========================
if (!isset($_GET['survey_id'])) {

    $surveys = mysqli_query($conn, "SELECT * FROM surveys ORDER BY survey_id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Question Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">

<h1>Question Management</h1>

<p>Select a survey to manage its questions.</p>

<table>

<tr>
    <th>ID</th>
    <th>Survey Title</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($surveys)){ ?>

<tr>

<td><?php echo $row['survey_id']; ?></td>

<td><?php echo htmlspecialchars($row['title']); ?></td>

<td><?php echo $row['status']; ?></td>

<td>

<a class="btn btn-success"
href="questions.php?survey_id=<?php echo $row['survey_id']; ?>">
Manage Questions
</a>

</td>

</tr>

<?php } ?>

</table>

<br>

<a href="dashboard.php" class="btn">← Dashboard</a>

</div>

</body>
</html>

<?php
exit();
}

// =========================
// SURVEY SELECTED
// =========================

$surveyID = intval($_GET['survey_id']);

$surveyResult = mysqli_query($conn,
"SELECT * FROM surveys WHERE survey_id='$surveyID'");

if(mysqli_num_rows($surveyResult)==0){

    die("Survey not found.");

}

$survey = mysqli_fetch_assoc($surveyResult);

$questions = mysqli_query($conn,

"SELECT * FROM survey_questions

WHERE survey_id='$surveyID'

ORDER BY question_id ASC");

?>

<!DOCTYPE html>

<html>

<head>

<title>Question Management</title>

<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<div class="container">

<h1>

Questions for:

<?php echo htmlspecialchars($survey['title']); ?>

</h1>

<a
class="btn btn-success"
href="add_question.php?survey_id=<?php echo $surveyID; ?>">

+ Add Question

</a>

<br><br>

<table>

<tr>

<th>ID</th>

<th>Question</th>

<th>Type</th>

<th>Action</th>

</tr>

<?php

if(mysqli_num_rows($questions)>0){

while($row=mysqli_fetch_assoc($questions)){

?>

<tr>

<td><?php echo $row['question_id']; ?></td>

<td><?php echo htmlspecialchars($row['question']); ?></td>

<td><?php echo $row['question_type']; ?></td>

<td>

<td>

<td>

<a class="btn btn-edit"
href="edit_question.php?id=<?php echo $row['question_id']; ?>">
Edit
</a>

<a class="btn"
href="view_choices.php?id=<?php echo $row['question_id']; ?>">
Choices
</a>

<a class="btn btn-delete"
href="../process/delete_question.php?id=<?php echo $row['question_id']; ?>"
onclick="return confirm('Delete this question?');">
Delete
</a>

</td>

</td>

</td>

</tr>

<?php
}

}else{
?>

<tr>

<td colspan="4">

No questions found.

</td>

</tr>

<?php
}
?>

</table>

<br>

<a
class="btn"
href="questions.php">

← Back to Survey List

</a>

</div>

</body>

</html>