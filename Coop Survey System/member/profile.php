<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_member_login();

$member_id = $_SESSION['member_id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($full_name === '') {
        $errors[] = 'Full name is required.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE members SET full_name = ?, email = ?, contact_number = ?, address = ?
            WHERE member_id = ?
        ");
        $stmt->execute([$full_name, $email, $contact_number, $address, $member_id]);
        $_SESSION['member_name'] = $full_name;
        set_flash('success', 'Your profile was updated successfully.');
        redirect('profile.php');
    }
}

$stmt = $pdo->prepare("SELECT * FROM members WHERE member_id = ?");
$stmt->execute([$member_id]);
$member = $stmt->fetch();

$page_title = 'My Profile';
$active = 'profile';
require_once '../includes/header_member.php';
?>

<h4 class="fw-bold mb-4">My Profile</h4>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-body">

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $err): ?><li><?php echo clean($err); ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="POST" novalidate>
          <div class="mb-3">
            <label class="form-label">Cooperative Account Number</label>
            <input type="text" class="form-control" value="<?php echo clean($member['account_number']); ?>" disabled>
          </div>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" required value="<?php echo clean($member['full_name']); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Email Address</label>
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
          <button type="submit" class="btn btn-coop">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
