<?php

include("../config/database.php");

$id = $_GET['id'];

$result = mysqli_query($conn,
"SELECT survey_id FROM survey_questions WHERE question_id='$id'");

$row = mysqli_fetch_assoc($result);

mysqli_query($conn,
"DELETE FROM survey_questions WHERE question_id='$id'");

header("Location: ../staff/questions.php?survey_id=".$row['survey_id']);

exit();
?>