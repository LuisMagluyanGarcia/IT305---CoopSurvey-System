<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

if (($_SESSION['staff_role'] ?? '') !== 'admin') {
    set_flash('error', 'Only administrators can access User Management.');
    redirect('dashboard.php');
}

$staffAccounts = $pdo->query("SELECT * FROM staff ORDER BY full_name")->fetchAll();

$page_title = 'User Management';
$active = 'users';
require_once '../includes/header_staff.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0">User Management</h4>
  <a href="user_create.php" class="btn btn-dark">Add Staff Account</a>
</div>

<div class="card">
  <div class="card-body">
    <?php if (empty($staffAccounts)): ?>
      <p class="text-muted mb-0">No staff accounts found.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>Account Number</th><th>Full Name</th><th>Email</th><th>Role</th><th>Status</th><th>Password</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($staffAccounts as $s): ?>
            <tr>
              <td><?php echo clean($s['username']); ?></td>
              <td><?php echo clean($s['full_name']); ?></td>
              <td><?php echo clean($s['email']); ?></td>
              <td><span class="badge text-bg-<?php echo $s['role'] === 'admin' ? 'dark' : 'secondary'; ?>"><?php echo ucfirst($s['role']); ?></span></td>
              <td>
                <?php if ($s['status'] === 'active'): ?>
                  <span class="badge text-bg-success">Active</span>
                <?php else: ?>
                  <span class="badge text-bg-secondary">Inactive</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ((int)$s['first_login'] === 1): ?>
                  <span class="badge text-bg-warning" title="Still using the auto-generated default password">Default (pending)</span>
                <?php else: ?>
                  <span class="badge text-bg-light border">Changed</span>
                <?php endif; ?>
              </td>
              <td class="text-nowrap">
                <a href="user_edit.php?id=<?php echo (int)$s['staff_id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                <?php if ((int)$s['staff_id'] !== (int)$_SESSION['staff_id']): ?>
                  <form action="user_toggle_status.php" method="POST" class="d-inline" data-confirm="<?php echo $s['status'] === 'active' ? 'Deactivate' : 'Activate'; ?> this staff account?">
                    <input type="hidden" name="staff_id" value="<?php echo (int)$s['staff_id']; ?>">
                    <?php if ($s['status'] === 'active'): ?>
                      <button type="submit" class="btn btn-sm btn-outline-warning">Deactivate</button>
                    <?php else: ?>
                      <button type="submit" class="btn btn-sm btn-outline-success">Activate</button>
                    <?php endif; ?>
                  </form>
                <?php else: ?>
                  <span class="text-muted small">(you)</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
