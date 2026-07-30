<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

if (($_SESSION['staff_role'] ?? '') !== 'admin') {
    set_flash('error', 'Only administrators can access User Management.');
    redirect('dashboard.php');
}

$staff_id = (int)($_GET['id'] ?? $_POST['staff_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM staff WHERE staff_id = ?");
$stmt->execute([$staff_id]);
$staffAccount = $stmt->fetch();

if (!$staffAccount) {
    set_flash('error', 'Staff account not found.');
    redirect('user_management.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = in_array($_POST['role'] ?? '', ['admin','staff']) ? $_POST['role'] : $staffAccount['role'];

    if ($full_name === '') $errors[] = 'Full name is required.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE staff SET full_name = ?, email = ?, role = ? WHERE staff_id = ?");
        $stmt->execute([$full_name, $email, $role, $staff_id]);
        if ($staff_id === (int)$_SESSION['staff_id']) {
            $_SESSION['staff_name'] = $full_name;
            $_SESSION['staff_role'] = $role;
        }
        set_flash('success', 'Staff account updated.');
        redirect('user_edit.php?id=' . $staff_id);
    }
    $staffAccount = array_merge($staffAccount, compact('full_name','email','role'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($new_password) < 6) {
        $errors[] = 'New password must be at least 6 characters long.';
    } elseif ($new_password !== $confirm) {
        $errors[] = 'Password and confirmation do not match.';
    } else {
        $hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE staff SET password = ?, first_login = 1 WHERE staff_id = ?");
        $stmt->execute([$hash, $staff_id]);
        set_flash('success', 'Password reset successfully. They\'ll be reminded to change it again on next login.');
        redirect('user_edit.php?id=' . $staff_id);
    }
}

$page_title = 'Edit Staff Account';
$active = 'users';
require_once '../includes/header_staff.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0">Edit Staff Account</h4>
  <a href="user_management.php" class="btn btn-outline-secondary btn-sm">&larr; Back</a>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?php echo clean($e); ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<div class="row g-3 justify-content-center">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header bg-white fw-bold">Account Details</div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="update_profile" value="1">
          <input type="hidden" name="staff_id" value="<?php echo (int)$staff_id; ?>">
          <div class="mb-3">
            <label class="form-label">Account Number</label>
            <input type="text" class="form-control" value="<?php echo clean($staffAccount['username']); ?>" disabled>
            <div class="form-text">
              Password status:
              <?php if ((int)$staffAccount['first_login'] === 1): ?>
                <span class="badge text-bg-warning">Still using default password</span>
              <?php else: ?>
                <span class="badge text-bg-light border">Password changed</span>
              <?php endif; ?>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" required value="<?php echo clean($staffAccount['full_name']); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo clean($staffAccount['email']); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" <?php echo (int)$staff_id === (int)$_SESSION['staff_id'] ? 'disabled' : ''; ?>>
              <option value="staff" <?php echo $staffAccount['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
              <option value="admin" <?php echo $staffAccount['role'] === 'admin' ? 'selected' : ''; ?>>Administrator</option>
            </select>
            <?php if ((int)$staff_id === (int)$_SESSION['staff_id']): ?>
              <input type="hidden" name="role" value="<?php echo clean($staffAccount['role']); ?>">
              <div class="form-text">You cannot change your own role.</div>
            <?php endif; ?>
          </div>
          <button type="submit" class="btn btn-coop">Save Changes</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-header bg-white fw-bold">Reset Password</div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="reset_password" value="1">
          <input type="hidden" name="staff_id" value="<?php echo (int)$staff_id; ?>">
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" required minlength="6">
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required minlength="6">
          </div>
          <button type="submit" class="btn btn-outline-warning w-100">Reset Password</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
