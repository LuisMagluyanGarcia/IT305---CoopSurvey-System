<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (is_staff_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM staff WHERE username = ?");
        $stmt->execute([$username]);
        $staff = $stmt->fetch();

        if (!$staff || !password_verify($password, $staff['password'])) {
            $error = 'Invalid username or password.';
        } elseif ($staff['status'] !== 'active') {
            $error = 'Your staff account has been deactivated.';
        } else {
            $_SESSION['staff_id'] = $staff['staff_id'];
            $_SESSION['staff_name'] = $staff['full_name'];
            $_SESSION['staff_role'] = $staff['role'];
            log_login($pdo, 'staff', $staff['staff_id']);
            redirect('dashboard.php');
        }
    }
}

$page_title = 'Staff Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Login - Cooperative Survey System</title>
<link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="staff-theme">
<div class="auth-wrapper" style="background: linear-gradient(135deg,#2b2f3a,#14161c);">
  <div class="card auth-card shadow-lg p-4">
    <div class="card-body">
      <h4 class="fw-bold text-center mb-1">Staff / Admin Login</h4>
      <p class="text-muted text-center mb-4">Cooperative Survey Management System</p>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo clean($error); ?></div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="mb-3">
          <label class="form-label">Staff Account Number</label>
          <input type="text" name="username" class="form-control" placeholder="e.g. STF-0002" required autofocus
                 value="<?php echo isset($_POST['username']) ? clean($_POST['username']) : ''; ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Default: your account number" required>
        </div>
        <button type="submit" class="btn btn-dark w-100">Log In</button>
      </form>
      <p class="text-muted small text-center mt-3 mb-0">Default admin: <code>admin</code> / <code>admin123</code></p>
      <p class="text-center mt-3 mb-0"><a href="../index.php">&larr; Back to portal selection</a></p>
    </div>
  </div>
</div>
</body>
</html>
