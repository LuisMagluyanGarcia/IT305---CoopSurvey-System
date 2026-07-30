<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: ../authentication/login.php");
    exit();
}

include("../config/database.php");

if (!isset($_GET['survey_id'])) {
    die("Survey not selected.");
}

$surveyID = intval($_GET['survey_id']);

$survey = mysqli_fetch_assoc(mysqli_query($conn,

"SELECT * FROM surveys
WHERE survey_id='$surveyID'"));

if(!$survey){

die("Survey not found.");

}

$sql="SELECT

responses.response_id,
responses.submitted_at,
members.member_id,
members.account_number,
members.fullname

FROM responses

INNER JOIN members

ON responses.member_id=members.member_id

WHERE responses.survey_id='$surveyID'

ORDER BY responses.submitted_at DESC";

$result=mysqli_query($conn,$sql);

?>

<!DOCTYPE html>

<html>

<head>

<title>Survey Respondents</title>

<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<div class="container">

<h1>

Respondents

</h1>

<h2>

<?php echo htmlspecialchars($survey['title']); ?>

</h2>

<table>

<tr>

<th>#</th>

<th>Account Number</th>

<th>Member Name</th>

<th>Date Submitted</th>

</tr>

<?php

$count=1;

if(mysqli_num_rows($result)>0){

while($row=mysqli_fetch_assoc($result)){

?>

<tr>

<td><?php echo $count++; ?></td>

<td><?php echo htmlspecialchars($row['account_number']); ?></td>

<td><?php echo htmlspecialchars($row['fullname']); ?></td>

<td><?php echo $row['submitted_at']; ?></td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="4">

No respondents yet.

</td>

</tr>

<?php

}

?>

</table>

<br>

<a
class="btn"
href="results.php">

← Back

</a>

</div>

</body>

</html>