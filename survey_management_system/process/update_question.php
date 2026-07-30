<?php

include("../config/database.php");

$questionID = $_POST['question_id'];
$surveyID = $_POST['survey_id'];
$question = mysqli_real_escape_string($conn,$_POST['question']);
$type = $_POST['question_type'];

$sql = "UPDATE survey_questions
SET
question='$question',
question_type='$type'
WHERE question_id='$questionID'";

mysqli_query($conn,$sql);

header("Location: ../staff/questions.php?survey_id=".$surveyID);

exit();
?>