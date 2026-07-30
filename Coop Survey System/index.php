<?php require_once 'config/database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cooperative Survey Management System</title>
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="card auth-card shadow-lg p-4">
    <div class="card-body text-center">
      <h4 class="fw-bold mb-1">Cooperative Survey Management System</h4>
      <p class="text-muted mb-4">Select your portal to continue</p>
      <div class="d-grid gap-3">
        <a href="member/login.php" class="btn btn-coop btn-lg">
          Member Portal
        </a>
        <a href="staff/login.php" class="btn btn-outline-dark btn-lg">
          Cooperative Staff Portal
        </a>
      </div>
      <p class="text-muted small mt-4 mb-0">IT 305 &mdash; Advance Web Development &middot; Activity 5 Set B</p>
    </div>
  </div>
</div>
</body>
</html>
