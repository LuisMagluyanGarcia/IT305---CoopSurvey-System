<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_member_login();

$member_id = $_SESSION['member_id'];
$stmt = $pdo->prepare("SELECT * FROM members WHERE member_id = ?");
$stmt->execute([$member_id]);
$member = $stmt->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $member['password'])) {
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
        $stmt = $pdo->prepare("UPDATE members SET password = ?, first_login = 0 WHERE member_id = ?");
        $stmt->execute([$hash, $member_id]);
        set_flash('success', 'Your password has been updated successfully.');
        redirect('dashboard.php');
    }
}

$page_title = 'Change Password';
$active = 'password';
require_once '../includes/header_member.php';
?>

<h4 class="fw-bold mb-4">Change Password</h4>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">

        <?php if ((int)$member['first_login'] === 1): ?>
          <div class="alert alert-warning">This is your first login. Please set a new password to continue.</div>
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
            <div class="form-text">If this is your first login, use your account number.</div>
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
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
