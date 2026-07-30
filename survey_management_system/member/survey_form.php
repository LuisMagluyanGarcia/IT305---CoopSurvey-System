<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: ../authentication/login.php");
    exit();
}

include("../config/database.php");

if(!isset($_GET['survey_id'])){
    die("Survey not found.");
}

$surveyID = intval($_GET['survey_id']);

$survey = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT * FROM surveys WHERE survey_id='$surveyID'"));

if(!$survey){
    die("Survey not found.");
}

$questions = mysqli_query($conn,
"SELECT * FROM survey_questions
WHERE survey_id='$surveyID'
ORDER BY question_id ASC");
?>

<!DOCTYPE html>
<html>

<head>

<title><?php echo htmlspecialchars($survey['title']); ?></title>

<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<div class="container">

<h1><?php echo htmlspecialchars($survey['title']); ?></h1>

<p><?php echo htmlspecialchars($survey['description']); ?></p>

<form
action="../process/response_process.php"
method="POST">

<input
type="hidden"
name="survey_id"
value="<?php echo $surveyID; ?>">

<?php

while($question=mysqli_fetch_assoc($questions)){

?>

<div class="question-box">

<h3>

<?php echo htmlspecialchars($question['question']); ?>

</h3>

<?php

switch($question['question_type']){

case "Multiple Choice":

$choices=mysqli_query($conn,

"SELECT * FROM survey_choices
WHERE question_id='".$question['question_id']."'");

while($choice=mysqli_fetch_assoc($choices)){

?>

<label>

<input
type="radio"
name="answer[<?php echo $question['question_id']; ?>]"
value="<?php echo htmlspecialchars($choice['choice_text']); ?>"
required>

<?php echo htmlspecialchars($choice['choice_text']); ?>

</label>

<br>

<?php

}

break;

case "Yes/No":

?>

<label>

<input
type="radio"
name="answer[<?php echo $question['question_id']; ?>]"
value="Yes"
required>

Yes

</label>

<br>

<label>

<input
type="radio"
name="answer[<?php echo $question['question_id']; ?>]"
value="No">

No

</label>

<?php

break;

case "Rating Scale":

for($i=1;$i<=5;$i++){

?>

<label>

<input
type="radio"
name="answer[<?php echo $question['question_id']; ?>]"
value="<?php echo $i; ?>"
required>

<?php echo $i; ?>

</label>

<?php

}

break;

case "Short Answer":

?>

<textarea
name="answer[<?php echo $question['question_id']; ?>]"
rows="4"
required></textarea>

<?php

break;

}

?>

</div>

<br>

<?php

}

?>

<button
type="submit">

Submit Survey

</button>

</form>

</div>

</body>

</html>