<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

if (($_SESSION['staff_role'] ?? '') !== 'admin') {
    set_flash('error', 'Only administrators can access User Management.');
    redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = in_array($_POST['role'] ?? '', ['admin', 'staff']) ? $_POST['role'] : 'staff';

    if ($full_name === '') $errors[] = 'Full name is required.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert with a throwaway placeholder first so we can generate the
            // account number from the guaranteed-unique auto-increment ID,
            // then go back and set the real username/password in one update.
            $placeholderUser = 'PENDING-' . bin2hex(random_bytes(6));
            $placeholderHash = password_hash(bin2hex(random_bytes(6)), PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("
                INSERT INTO staff (username, password, full_name, email, role, status, first_login)
                VALUES (?, ?, ?, ?, ?, 'active', 1)
            ");
            $stmt->execute([$placeholderUser, $placeholderHash, $full_name, $email, $role]);
            $new_id = $pdo->lastInsertId();

            $account_number = 'STF-' . str_pad($new_id, 4, '0', STR_PAD_LEFT);
            $finalHash = password_hash($account_number, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("UPDATE staff SET username = ?, password = ? WHERE staff_id = ?");
            $stmt->execute([$account_number, $finalHash, $new_id]);

            $pdo->commit();

            set_flash('success', "Staff account created. Account Number: $account_number — this is also their default login password. Please share it with them directly; they'll be prompted to change it on first login.");
            redirect('user_management.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Could not create the staff account. Please try again.';
        }
    }
}

$page_title = 'Add Staff Account';
$active = 'users';
require_once '../includes/header_staff.php';
?>

<h4 class="fw-bold mb-4">Add Staff Account</h4>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?php echo clean($e); ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <div class="alert alert-info small">
          An account number will be generated automatically once you save (e.g. <code>STF-0003</code>).
          It doubles as the staff member's login username and default password.
        </div>
        <form method="POST" novalidate>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" required value="<?php echo isset($_POST['full_name']) ? clean($_POST['full_name']) : ''; ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? clean($_POST['email']) : ''; ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
              <option value="staff">Staff</option>
              <option value="admin">Administrator</option>
            </select>
          </div>
          <button type="submit" class="btn btn-coop">Generate Account</button>
          <a href="user_management.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
