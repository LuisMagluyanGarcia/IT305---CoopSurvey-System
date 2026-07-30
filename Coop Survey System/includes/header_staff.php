<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($page_title) ? clean($page_title) . ' - ' : ''; ?>Staff Portal - Cooperative Survey System</title>
<link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="staff-theme">
<?php if (is_staff_logged_in()): ?>
<nav class="navbar">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">Coop Survey &mdash; Staff</a>
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link <?php echo ($active ?? '') === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">Dashboard</a></li>
      <li class="nav-item"><a class="nav-link <?php echo ($active ?? '') === 'surveys' ? 'active' : ''; ?>" href="survey_list.php">Surveys</a></li>
      <li class="nav-item"><a class="nav-link <?php echo ($active ?? '') === 'members' ? 'active' : ''; ?>" href="member_list.php">Members</a></li>
      <li class="nav-item"><a class="nav-link <?php echo ($active ?? '') === 'results' ? 'active' : ''; ?>" href="results_dashboard.php">Results</a></li>
      <li class="nav-item"><a class="nav-link <?php echo ($active ?? '') === 'reports' ? 'active' : ''; ?>" href="reports.php">Reports</a></li>
      <?php if (($_SESSION['staff_role'] ?? '') === 'admin'): ?>
      <li class="nav-item"><a class="nav-link <?php echo ($active ?? '') === 'users' ? 'active' : ''; ?>" href="user_management.php">User Management</a></li>
      <?php endif; ?>
      <li class="nav-item"><a class="nav-link <?php echo ($active ?? '') === 'profile' ? 'active' : ''; ?>" href="profile.php">Profile</a></li>
      <li class="nav-item"><a class="nav-link logout-link" href="logout.php">Logout</a></li>
    </ul>
  </div>
</nav>
<?php endif; ?>
<main class="container-fluid px-4 py-4">
<?php render_flash(); ?>
