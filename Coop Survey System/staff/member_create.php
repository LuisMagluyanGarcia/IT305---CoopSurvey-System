<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_number = trim($_POST['account_number'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($account_number === '') $errors[] = 'Cooperative account number is required.';
    if ($full_name === '') $errors[] = 'Full name is required.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT member_id FROM members WHERE account_number = ?");
        $stmt->execute([$account_number]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this account number already exists.';
        }
    }

    if (empty($errors)) {
        // Default password = account number itself, as required by the spec.
        $hash = password_hash($account_number, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            INSERT INTO members (account_number, password, full_name, email, contact_number, address, first_login, status)
            VALUES (?, ?, ?, ?, ?, ?, 1, 'active')
        ");
        $stmt->execute([$account_number, $hash, $full_name, $email, $contact_number, $address]);
        set_flash('success', "Member account created. Default password is the account number ($account_number).");
        redirect('member_list.php');
    }
}

$page_title = 'Add Member';
$active = 'members';
require_once '../includes/header_staff.php';
?>

<h4 class="fw-bold mb-4">Add Cooperative Member</h4>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?php echo clean($e); ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-body">
        <form method="POST" novalidate>
          <div class="mb-3">
            <label class="form-label">Cooperative Account Number</label>
            <input type="text" name="account_number" class="form-control" required value="<?php echo isset($_POST['account_number']) ? clean($_POST['account_number']) : ''; ?>">
            <div class="form-text">This will also be used as the member's login username and default password.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" required value="<?php echo isset($_POST['full_name']) ? clean($_POST['full_name']) : ''; ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? clean($_POST['email']) : ''; ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Contact Number</label>
            <input type="text" name="contact_number" class="form-control" value="<?php echo isset($_POST['contact_number']) ? clean($_POST['contact_number']) : ''; ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="2"><?php echo isset($_POST['address']) ? clean($_POST['address']) : ''; ?></textarea>
          </div>
          <button type="submit" class="btn btn-coop">Create Account</button>
          <a href="member_list.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
