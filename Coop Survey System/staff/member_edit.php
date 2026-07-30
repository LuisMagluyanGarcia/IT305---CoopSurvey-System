<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

$member_id = (int)($_GET['id'] ?? $_POST['member_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM members WHERE member_id = ?");
$stmt->execute([$member_id]);
$member = $stmt->fetch();

if (!$member) {
    set_flash('error', 'Member not found.');
    redirect('member_list.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : $member['status'];

    if ($full_name === '') $errors[] = 'Full name is required.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE members SET full_name = ?, email = ?, contact_number = ?, address = ?, status = ?
            WHERE member_id = ?
        ");
        $stmt->execute([$full_name, $email, $contact_number, $address, $status, $member_id]);
        set_flash('success', 'Member updated successfully.');
        redirect('member_edit.php?id=' . $member_id);
    }
    $member = array_merge($member, compact('full_name','email','contact_number','address','status'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $hash = password_hash($member['account_number'], PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE members SET password = ?, first_login = 1 WHERE member_id = ?");
    $stmt->execute([$hash, $member_id]);
    set_flash('success', 'Password reset to the member\'s account number. They will be asked to set a new one on next login.');
    redirect('member_edit.php?id=' . $member_id);
}

$page_title = 'Edit Member';
$active = 'members';
require_once '../includes/header_staff.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0">Edit Member</h4>
  <a href="member_list.php" class="btn btn-outline-secondary btn-sm">&larr; Back</a>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?php echo clean($e); ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<div class="row g-3 justify-content-center">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header bg-white fw-bold">Profile Details</div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="update_profile" value="1">
          <input type="hidden" name="member_id" value="<?php echo (int)$member_id; ?>">
          <div class="mb-3">
            <label class="form-label">Cooperative Account Number</label>
            <input type="text" class="form-control" value="<?php echo clean($member['account_number']); ?>" disabled>
          </div>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" required value="<?php echo clean($member['full_name']); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo clean($member['email']); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Contact Number</label>
            <input type="text" name="contact_number" class="form-control" value="<?php echo clean($member['contact_number']); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="2"><?php echo clean($member['address']); ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="active" <?php echo $member['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
              <option value="inactive" <?php echo $member['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
          </div>
          <button type="submit" class="btn btn-coop">Save Changes</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-header bg-white fw-bold">Password</div>
      <div class="card-body">
        <p class="text-muted small">Reset this member's password back to their account number. They'll be required to change it on next login.</p>
        <form method="POST" data-confirm="Reset this member's password to their account number?">
          <input type="hidden" name="reset_password" value="1">
          <input type="hidden" name="member_id" value="<?php echo (int)$member_id; ?>">
          <button type="submit" class="btn btn-outline-warning w-100">Reset Password</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
