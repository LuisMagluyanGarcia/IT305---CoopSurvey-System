<?php

include("../config/database.php");

$id=$_GET['id'];

$result=mysqli_query($conn,

"SELECT * FROM survey_choices
WHERE question_id='$id'");

?>

<!DOCTYPE html>

<html>

<head>

<title>Question Choices</title>

<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<div class="container">

<h1>Choices</h1>

<table>

<tr>

<th>ID</th>

<th>Choice</th>

</tr>

<?php

while($row=mysqli_fetch_assoc($result)){

?>

<tr>

<td><?php echo $row['choice_id']; ?></td>

<td><?php echo htmlspecialchars($row['choice_text']); ?></td>

</tr>

<?php

}

?>

</table>

<br>

<a class="btn" href="javascript:history.back()">

Back

</a>

</div>

</body>

</html>