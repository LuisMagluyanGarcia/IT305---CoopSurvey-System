<?php
session_start();

include("../config/database.php");

if(!isset($_GET['survey_id'])){
    die("Survey not found.");
}

$surveyID = intval($_GET['survey_id']);

$survey = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT * FROM surveys WHERE survey_id='$surveyID'"));

$questions = mysqli_query($conn,
"SELECT * FROM survey_questions
WHERE survey_id='$surveyID'
ORDER BY question_id ASC");
?>

<!DOCTYPE html>
<html>

<head>

<title>Survey Results</title>

<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<div class="container">

<h1>

Results for

<?php echo htmlspecialchars($survey['title']); ?>

</h1>

<?php

while($question=mysqli_fetch_assoc($questions)){

?>

<div class="question-box">

<h3>

<?php echo htmlspecialchars($question['question']); ?>

</h3>

<?php

$answers = mysqli_query($conn,

"SELECT answer,
COUNT(*) AS total

FROM response_answers

WHERE question_id='".$question['question_id']."'

GROUP BY answer");

if(mysqli_num_rows($answers)>0){

?>

<table>

<tr>

<th>Answer</th>

<th>Total Responses</th>

</tr>

<?php

while($row=mysqli_fetch_assoc($answers)){

?>

<tr>

<td><?php echo htmlspecialchars($row['answer']); ?></td>

<td><?php echo $row['total']; ?></td>

</tr>

<?php

}

?>

</table>

<?php

}else{

echo "<p>No responses yet.</p>";

}

?>

</div>

<br>

<?php

}

?>

<a
class="btn"
href="results.php">

← Back

</a>

</div>

</body>

</html>