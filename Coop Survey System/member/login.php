<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (is_member_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_number = trim($_POST['account_number'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($account_number === '' || $password === '') {
        $error = 'Please enter both your account number and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM members WHERE account_number = ?");
        $stmt->execute([$account_number]);
        $member = $stmt->fetch();

        if (!$member || !password_verify($password, $member['password'])) {
            $error = 'Invalid account number or password.';
        } elseif ($member['status'] !== 'active') {
            $error = 'Your account has been deactivated. Please contact the cooperative staff.';
        } else {
            $_SESSION['member_id'] = $member['member_id'];
            $_SESSION['member_name'] = $member['full_name'];
            log_login($pdo, 'member', $member['member_id']);

            if ((int)$member['first_login'] === 1) {
                set_flash('info', 'This is your first login. Please change your password to continue.');
                redirect('change_password.php');
            }
            redirect('dashboard.php');
        }
    }
}

$page_title = 'Member Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Member Login - Cooperative Survey System</title>
<link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="card auth-card shadow-lg p-4">
    <div class="card-body">
      <h4 class="fw-bold text-center mb-1">Member Login</h4>
      <p class="text-muted text-center mb-4">Cooperative Survey Management System</p>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo clean($error); ?></div>
      <?php endif; ?>
      <?php render_flash(); ?>

      <form method="POST" novalidate>
        <div class="mb-3">
          <label class="form-label">Cooperative Account Number</label>
          <input type="text" name="account_number" class="form-control" placeholder="e.g. 2024-0001" required autofocus
                 value="<?php echo isset($_POST['account_number']) ? clean($_POST['account_number']) : ''; ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Default: your account number" required>
        </div>
        <button type="submit" class="btn btn-coop w-100">Log In</button>
      </form>
      <p class="text-muted small text-center mt-3 mb-0">
        First time logging in? Use your account number as your password.
      </p>
      <p class="text-center mt-3 mb-0"><a href="../index.php">&larr; Back to portal selection</a></p>
    </div>
  </div>
</div>
</body>
</html>
