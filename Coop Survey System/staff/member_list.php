<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM members
        WHERE account_number LIKE ? OR full_name LIKE ? OR email LIKE ?
        ORDER BY full_name
    ");
    $like = "%$search%";
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $pdo->query("SELECT * FROM members ORDER BY full_name");
}
$members = $stmt->fetchAll();

$page_title = 'Member Management';
$active = 'members';
require_once '../includes/header_staff.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0">Member Management</h4>
  <a href="member_create.php" class="btn btn-dark">Add Member</a>
</div>

<form method="GET" class="mb-3">
  <div class="input-group" style="max-width:400px;">
    <input type="text" name="q" class="form-control" placeholder="Search by account #, name, or email" value="<?php echo clean($search); ?>">
    <button type="submit" class="btn btn-outline-dark">Search</button>
  </div>
</form>

<div class="card">
  <div class="card-body">
    <?php if (empty($members)): ?>
      <p class="text-muted mb-0">No members found.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>Account #</th><th>Name</th><th>Email</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($members as $m): ?>
            <tr>
              <td><?php echo clean($m['account_number']); ?></td>
              <td><?php echo clean($m['full_name']); ?></td>
              <td><?php echo clean($m['email']); ?></td>
              <td>
                <?php if ($m['status'] === 'active'): ?>
                  <span class="badge text-bg-success">Active</span>
                <?php else: ?>
                  <span class="badge text-bg-secondary">Inactive</span>
                <?php endif; ?>
              </td>
              <td class="text-nowrap">
                <a href="member_edit.php?id=<?php echo (int)$m['member_id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form action="member_toggle_status.php" method="POST" class="d-inline" data-confirm="<?php echo $m['status'] === 'active' ? 'Deactivate' : 'Activate'; ?> this member?">
                  <input type="hidden" name="member_id" value="<?php echo (int)$m['member_id']; ?>">
                  <?php if ($m['status'] === 'active'): ?>
                    <button type="submit" class="btn btn-sm btn-outline-warning">Deactivate</button>
                  <?php else: ?>
                    <button type="submit" class="btn btn-sm btn-outline-success">Activate</button>
                  <?php endif; ?>
                </form>
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
