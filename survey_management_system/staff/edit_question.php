<?php
session_start();
include("../config/database.php");

if (!isset($_GET['id'])) {
    die("Question not found.");
}

$id = intval($_GET['id']);

$result = mysqli_query($conn, "SELECT * FROM survey_questions WHERE question_id = $id");

if (mysqli_num_rows($result) == 0) {
    die("Question not found.");
}

$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>

<head>

<title>Edit Question</title>

<link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="container">

<h1>Edit Question</h1>

<form action="../process/update_question.php" method="POST">

<input type="hidden" name="question_id" value="<?php echo $row['question_id']; ?>">

<input type="hidden" name="survey_id" value="<?php echo $row['survey_id']; ?>">

<label>Question</label>

<textarea name="question" required><?php echo htmlspecialchars($row['question']); ?></textarea>

<label>Question Type</label>

<select name="question_type">

<option value="Multiple Choice" <?php if($row['question_type']=="Multiple Choice") echo "selected"; ?>>Multiple Choice</option>

<option value="Yes/No" <?php if($row['question_type']=="Yes/No") echo "selected"; ?>>Yes/No</option>

<option value="Rating Scale" <?php if($row['question_type']=="Rating Scale") echo "selected"; ?>>Rating Scale</option>

<option value="Short Answer" <?php if($row['question_type']=="Short Answer") echo "selected"; ?>>Short Answer</option>

</select>

<br><br>

<button type="submit">Update Question</button>

</form>

</div>

</body>

</html>