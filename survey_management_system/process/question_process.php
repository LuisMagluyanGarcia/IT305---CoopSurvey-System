<?php

include("../config/database.php");

$surveyID=$_POST['survey_id'];

$question=mysqli_real_escape_string($conn,$_POST['question']);

$type=$_POST['question_type'];

$sql="INSERT INTO survey_questions
(
survey_id,
question,
question_type
)
VALUES
(
'$surveyID',
'$question',
'$type'
)";

mysqli_query($conn,$sql);

$questionID=mysqli_insert_id($conn);

if($type=="Multiple Choice"){

    foreach($_POST['choice'] as $choice){

        $choice=trim($choice);

        if($choice!=""){

            $choice=mysqli_real_escape_string($conn,$choice);

            mysqli_query($conn,

            "INSERT INTO survey_choices
            (
            question_id,
            choice_text
            )
            VALUES
            (
            '$questionID',
            '$choice'
            )");

        }

    }

}

header("Location: ../staff/questions.php?survey_id=".$surveyID);

exit();

?>