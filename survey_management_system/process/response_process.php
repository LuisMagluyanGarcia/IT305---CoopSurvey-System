<?php

session_start();

include("../config/database.php");

$surveyID = $_POST['survey_id'];

$memberID = $_SESSION['user_id'];

/* Prevent duplicate submissions */

$check = mysqli_query($conn,

"SELECT * FROM responses
WHERE survey_id='$surveyID'
AND member_id='$memberID'");

if(mysqli_num_rows($check)>0){

die("You have already answered this survey.");

}

/* Save survey response */

mysqli_query($conn,

"INSERT INTO responses
(
survey_id,
member_id
)
VALUES
(
'$surveyID',
'$memberID'
)");

$responseID = mysqli_insert_id($conn);

/* Save each answer */

foreach($_POST['answer'] as $questionID=>$answer){

$answer = mysqli_real_escape_string($conn,$answer);

mysqli_query($conn,

"INSERT INTO response_answers
(
response_id,
question_id,
answer
)
VALUES
(
'$responseID',
'$questionID',
'$answer'
)");

}

header("Location: ../member/submission_success.php");

exit();

?>