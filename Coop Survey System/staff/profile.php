<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

$staff_id = $_SESSION['staff_id'];
$stmt = $pdo->prepare("SELECT * FROM staff WHERE staff_id = ?");
$stmt->execute([$staff_id]);
$staffAccount = $stmt->fetch();

$uploadDir = __DIR__ . '/uploads/profile_photos/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$errors = [];

/* ---------- Update name / email ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($full_name === '') $errors[] = 'Full name is required.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE staff SET full_name = ?, email = ? WHERE staff_id = ?");
        $stmt->execute([$full_name, $email, $staff_id]);
        $_SESSION['staff_name'] = $full_name;
        set_flash('success', 'Your profile was updated successfully.');
        redirect('profile.php');
    } else {
        $staffAccount['full_name'] = $full_name;
        $staffAccount['email'] = $email;
    }
}

/* ---------- Change password ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
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
        redirect('profile.php');
    }
}

/* ---------- Upload profile photo ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    $file = $_FILES['profile_photo'] ?? null;
    $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 3 * 1024 * 1024; // 3MB

    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Please choose an image file first.';
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'There was a problem uploading your photo.';
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt)) {
            $errors[] = 'Please upload a JPG, PNG, GIF, or WEBP image.';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'Image must be smaller than 3MB.';
        } elseif (!@getimagesize($file['tmp_name'])) {
            $errors[] = 'The uploaded file is not a valid image.';
        }
    }

    if (empty($errors)) {
        $newFilename = 'staff_' . $staff_id . '_' . time() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFilename)) {
            if (!empty($staffAccount['profile_photo'])) {
                $oldPath = $uploadDir . $staffAccount['profile_photo'];
                if (is_file($oldPath)) @unlink($oldPath);
            }
            $stmt = $pdo->prepare("UPDATE staff SET profile_photo = ? WHERE staff_id = ?");
            $stmt->execute([$newFilename, $staff_id]);
            set_flash('success', 'Profile photo updated.');
            redirect('profile.php');
        } else {
            $errors[] = 'Failed to save the uploaded photo. Please try again.';
        }
    }
}

/* ---------- Remove profile photo ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_photo'])) {
    if (!empty($staffAccount['profile_photo'])) {
        $oldPath = $uploadDir . $staffAccount['profile_photo'];
        if (is_file($oldPath)) @unlink($oldPath);
        $stmt = $pdo->prepare("UPDATE staff SET profile_photo = NULL WHERE staff_id = ?");
        $stmt->execute([$staff_id]);
    }
    set_flash('success', 'Profile photo removed.');
    redirect('profile.php');
}

// Note: every successful update above redirects immediately, so by the time
// we reach here (GET request, or a POST that failed validation) $staffAccount
// already holds the right data to render — either the original DB row, or
// the DB row merged with the user's just-submitted (but invalid) input.

$page_title = 'My Profile';
$active = 'profile';
require_once '../includes/header_staff.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0">My Profile</h4>
  <a href="profile_pdf.php" class="btn btn-outline-dark btn-sm" target="_blank">
    Download as PDF
  </a>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?php echo clean($e); ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<div class="row g-3">
  <!-- Photo + account summary -->
  <div class="col-lg-4">
    <div class="card text-center">
      <div class="card-body">
        <?php if (!empty($staffAccount['profile_photo'])): ?>
          <img src="uploads/profile_photos/<?php echo clean($staffAccount['profile_photo']); ?>"
               alt="Profile photo" class="rounded-circle mb-3"
               style="width:150px;height:150px;object-fit:cover;border:3px solid #1e6b3e;">
        <?php else: ?>
          <div class="rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center"
               style="width:150px;height:150px;background:#e9ecef;border:3px solid #1e6b3e;">
            <span style="font-size:3rem;font-weight:bold;color:#888;"><?php echo strtoupper(substr($staffAccount['full_name'], 0, 1)); ?></span>
          </div>
        <?php endif; ?>

        <h5 class="fw-bold mb-0"><?php echo clean($staffAccount['full_name']); ?></h5>
        <p class="text-muted mb-2"><?php echo clean($staffAccount['username']); ?></p>
        <span class="badge text-bg-<?php echo $staffAccount['role'] === 'admin' ? 'dark' : 'secondary'; ?>">
          <?php echo ucfirst($staffAccount['role']); ?>
        </span>
        <span class="badge text-bg-<?php echo $staffAccount['status'] === 'active' ? 'success' : 'secondary'; ?>">
          <?php echo ucfirst($staffAccount['status']); ?>
        </span>

        <hr>

        <form method="POST" enctype="multipart/form-data" class="text-start">
          <input type="hidden" name="upload_photo" value="1">
          <label class="form-label small fw-semibold">Update Profile Photo</label>
          <input type="file" name="profile_photo" class="form-control form-control-sm mb-2" accept=".jpg,.jpeg,.png,.gif,.webp" required>
          <div class="form-text mb-2">JPG, PNG, GIF, or WEBP. Max 3MB.</div>
          <button type="submit" class="btn btn-sm btn-coop w-100">
            Upload Photo
          </button>
        </form>

        <?php if (!empty($staffAccount['profile_photo'])): ?>
          <form method="POST" class="mt-2" data-confirm="Remove your profile photo?">
            <input type="hidden" name="remove_photo" value="1">
            <button type="submit" class="btn btn-sm btn-outline-danger w-100">
              Remove Photo
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Account details + password -->
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header bg-white fw-bold">Account Information</div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="update_profile" value="1">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Account Number</label>
              <input type="text" class="form-control" value="<?php echo clean($staffAccount['username']); ?>" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label">Role</label>
              <input type="text" class="form-control" value="<?php echo ucfirst(clean($staffAccount['role'])); ?>" disabled>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" required value="<?php echo clean($staffAccount['full_name']); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" value="<?php echo clean($staffAccount['email']); ?>">
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <input type="text" class="form-control" value="<?php echo ucfirst(clean($staffAccount['status'])); ?>" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label">Account Created</label>
              <input type="text" class="form-control" value="<?php echo date('M d, Y', strtotime($staffAccount['created_at'])); ?>" disabled>
            </div>
          </div>
          <button type="submit" class="btn btn-coop">Save Changes</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
        <span>Password</span>
        <?php if ((int)$staffAccount['first_login'] === 1): ?>
          <span class="badge text-bg-warning">Still using default password</span>
        <?php else: ?>
          <span class="badge text-bg-light border">Password changed</span>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <form method="POST" novalidate>
          <input type="hidden" name="update_password" value="1">
          <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
            <div class="form-text">If you haven't changed it yet, use your account number.</div>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">New Password</label>
              <input type="password" name="new_password" class="form-control" required minlength="6">
            </div>
            <div class="col-md-6">
              <label class="form-label">Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control" required minlength="6">
            </div>
          </div>
          <button type="submit" class="btn btn-outline-warning mt-3">Update Password</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
