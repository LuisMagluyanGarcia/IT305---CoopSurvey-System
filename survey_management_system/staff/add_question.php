<?php
session_start();

$surveyID = $_GET['survey_id'];
?>

<!DOCTYPE html>

<html>

<head>

<title>Add Question</title>

<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<div class="container">

<h1>Add Question</h1>

<form action="../process/question_process.php" method="POST">

<input type="hidden" name="survey_id" value="<?php echo $surveyID; ?>">

<label>Question</label>

<textarea name="question" required></textarea>

<label>Question Type</label>

<select name="question_type" id="questionType" onchange="toggleChoices()">

    <option value="Multiple Choice">Multiple Choice</option>

    <option value="Yes/No">Yes/No</option>

    <option value="Rating Scale">Rating Scale</option>

    <option value="Short Answer">Short Answer</option>

</select>

<div id="choicesBox">

<h3>Choices</h3>

<div id="choicesContainer">

<input
type="text"
name="choice[]"
placeholder="Choice 1"
required>

<input
type="text"
name="choice[]"
placeholder="Choice 2"
required>

</div>

<br>

<button
type="button"
class="btn"
onclick="addChoice()">

+ Add Choice

</button>

</div>

<br>

<button type="submit">Save Question</button>

</form>

<script>

let choiceCount = 2;

function toggleChoices() {

    let type = document.getElementById("questionType").value;

    let box = document.getElementById("choicesBox");

    let inputs = box.querySelectorAll('input[type="text"]');

    if (type === "Multiple Choice") {

        box.style.display = "block";

        inputs.forEach(input => input.required = true);

    } else {

        box.style.display = "none";

        inputs.forEach(input => input.required = false);

    }

}

function addChoice() {

    choiceCount++;

    let input = document.createElement("input");

    input.type = "text";

    input.name = "choice[]";

    input.placeholder = "Choice " + choiceCount;

    input.required = true;

    document.getElementById("choicesContainer").appendChild(input);

}

toggleChoices();

</script>

</div>

</body>

</html>