<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($page_title) ? clean($page_title) . ' - ' : ''; ?>Cooperative Survey System</title>
<link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="member-theme">
<?php if (is_member_logged_in()): ?>
<nav class="navbar">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Coop Survey</a>
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link <?php echo ($active ?? '') === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">Dashboard</a></li>
      <li class="nav-item"><a class="nav-link <?php echo ($active ?? '') === 'surveys' ? 'active' : ''; ?>" href="available_surveys.php">Available Surveys</a></li>
      <li class="nav-item"><a class="nav-link <?php echo ($active ?? '') === 'profile' ? 'active' : ''; ?>" href="profile.php">Profile</a></li>
      <li class="nav-item"><a class="nav-link <?php echo ($active ?? '') === 'password' ? 'active' : ''; ?>" href="change_password.php">Change Password</a></li>
      <li class="nav-item"><a class="nav-link logout-link" href="logout.php">Logout</a></li>
    </ul>
  </div>
</nav>
<?php endif; ?>
<main class="container py-4">
<?php render_flash(); ?>
