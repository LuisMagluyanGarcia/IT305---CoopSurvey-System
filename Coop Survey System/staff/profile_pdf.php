<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

$staff_id = $_SESSION['staff_id'];
$stmt = $pdo->prepare("SELECT * FROM staff WHERE staff_id = ?");
$stmt->execute([$staff_id]);
$staffAccount = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile - <?php echo clean($staffAccount['full_name']); ?></title>
<link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="staff-theme">
<main class="container py-4" style="max-width:700px;">
  <div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <a href="profile.php" class="btn btn-outline-secondary btn-sm">&larr; Back to Profile</a>
    <button onclick="window.print()" class="btn btn-dark btn-sm">Print / Save as PDF</button>
  </div>

  <div class="text-center mb-4">
    <h4 class="fw-bold mb-0">Multipurpose Cooperative</h4>
    <p class="text-muted mb-0">Staff Profile Summary</p>
  </div>

  <div class="text-center mb-4">
    <?php if (!empty($staffAccount['profile_photo'])): ?>
      <img src="uploads/profile_photos/<?php echo clean($staffAccount['profile_photo']); ?>"
           alt="Profile photo" class="rounded-circle mb-2"
           style="width:130px;height:130px;object-fit:cover;border:3px solid #1e6b3e;">
    <?php else: ?>
      <div class="rounded-circle mb-2 mx-auto d-flex align-items-center justify-content-center"
           style="width:130px;height:130px;background:#e9ecef;border:3px solid #1e6b3e;">
        <span style="font-size:2.5rem;font-weight:bold;color:#888;"><?php echo strtoupper(substr($staffAccount['full_name'], 0, 1)); ?></span>
      </div>
    <?php endif; ?>
    <h5 class="fw-bold mb-0"><?php echo clean($staffAccount['full_name']); ?></h5>
    <p class="text-muted mb-0"><?php echo ucfirst(clean($staffAccount['role'])); ?></p>
  </div>

  <table class="table table-sm table-bordered">
    <tr><th style="width:220px;">Account Number</th><td><?php echo clean($staffAccount['username']); ?></td></tr>
    <tr><th>Full Name</th><td><?php echo clean($staffAccount['full_name']); ?></td></tr>
    <tr><th>Email</th><td><?php echo clean($staffAccount['email']); ?></td></tr>
    <tr><th>Role</th><td><?php echo ucfirst(clean($staffAccount['role'])); ?></td></tr>
    <tr><th>Status</th><td><?php echo ucfirst(clean($staffAccount['status'])); ?></td></tr>
    <tr><th>Account Created</th><td><?php echo date('M d, Y', strtotime($staffAccount['created_at'])); ?></td></tr>
    <tr><th>Report Generated</th><td><?php echo date('M d, Y g:i A'); ?></td></tr>
  </table>
</main>
</body>
</html>
