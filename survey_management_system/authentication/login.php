<?php
session_start();

if(isset($_SESSION['role'])){
    if($_SESSION['role']=="Admin" || $_SESSION['role']=="Staff"){
        header("Location: ../staff/dashboard.php");
    }else{
        header("Location: ../member/dashboard.php");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="login-container">

<h2>Survey Management System</h2>

<form action="../process/login_process.php" method="POST">

<label>Login As</label>

<select name="user_type" required>
    <option value="">Select</option>
    <option value="staff">Staff/Admin</option>
    <option value="member">Member</option>
</select>

<br><br>

<label>
<?php
echo "<script>
function changeLabel(value){
    document.getElementById('usernameLabel').innerHTML =
    value=='member' ? 'Account Number' : 'Username';
}
</script>";
?>

<span id="usernameLabel">Username</span>

</label>

<input type="text" name="username" required>

<br><br>

<label>Password</label>

<input type="password" name="password" required>

<br><br>

<button type="submit">Login</button>

</form>

</div>

<script>

document.querySelector("select").addEventListener("change",function(){
    document.getElementById("usernameLabel").innerHTML=
    this.value=="member"?"Account Number":"Username";
});

</script>

</body>
</html>