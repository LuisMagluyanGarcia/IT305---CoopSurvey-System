<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

$staff_id = $_SESSION['staff_id'];
$stmt = $pdo->prepare("SELECT * FROM staff WHERE staff_id = ?");
$stmt->execute([$staff_id]);
$staffAccount = $stmt->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $staffAccount['password'])) {
        $errors[] = 'Current password is incorrect.';
    }
    if (strlen($new) < 6) {
        $errors[] = 'New password must be at least 6 characters long.';
    }
    if ($new !== $confirm) {
        $errors[] = 'New password and confirmation do not match.';
    }

    if (empty($errors)) {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE staff SET password = ?, first_login = 0 WHERE staff_id = ?");
        $stmt->execute([$hash, $staff_id]);
        set_flash('success', 'Your password has been updated successfully.');
        redirect('dashboard.php');
    }
}

$page_title = 'Change Password';
$active = 'password';
require_once '../includes/header_staff.php';
?>

<h4 class="fw-bold mb-4"><i class="fa-solid fa-key"></i> Change Password</h4>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">

        <?php if ((int)$staffAccount['first_login'] === 1): ?>
          <div class="alert alert-warning">You're still using your auto-generated default password. We recommend setting a new one.</div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $err): ?><li><?php echo clean($err); ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="POST" novalidate>
          <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
            <div class="form-text">If you haven't changed it yet, use your account number.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" required minlength="6">
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required minlength="6">
          </div>
          <button type="submit" class="btn btn-coop">Update Password</button>
          <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
