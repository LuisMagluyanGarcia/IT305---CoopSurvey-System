<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_member_login();

$member_id = $_SESSION['member_id'];

// All surveys whose staff-set status is 'active' and are within the open/close window
$stmt = $pdo->prepare("
    SELECT s.*, (r.response_id IS NOT NULL) AS already_answered
    FROM surveys s
    LEFT JOIN responses r ON r.survey_id = s.survey_id AND r.member_id = ?
    WHERE s.status = 'active'
    ORDER BY s.open_date DESC
");
$stmt->execute([$member_id]);
$all_active = $stmt->fetchAll();

$available = [];
foreach ($all_active as $s) {
    if (get_effective_survey_status($s) === 'active' && !$s['already_answered']) {
        $available[] = $s;
    }
}

$stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM responses WHERE member_id = ?");
$stmt->execute([$member_id]);
$answered_count = $stmt->fetch()['c'];

$page_title = 'Dashboard';
$active = 'dashboard';
require_once '../includes/header_member.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-0">Welcome, <?php echo clean($_SESSION['member_name']); ?> 👋</h4>
    <p class="text-muted mb-0">Here's what's happening with your cooperative surveys.</p>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card stat-card bg-green p-3">
      <div class="stat-value"><?php echo count($available); ?></div>
      <div>Surveys Awaiting Your Response</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card bg-gold p-3">
      <div class="stat-value"><?php echo (int)$answered_count; ?></div>
      <div>Surveys You've Completed</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card bg-navy p-3">
      <div class="stat-value"><?php echo count($all_active); ?></div>
      <div>Total Active Surveys</div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header bg-white fw-bold">
    Surveys Awaiting Your Response
  </div>
  <div class="card-body">
    <?php if (empty($available)): ?>
      <p class="text-muted mb-0">You're all caught up! There are no pending surveys right now.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>Title</th><th>Closes</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($available as $s): ?>
            <tr>
              <td>
                <strong><?php echo clean($s['title']); ?></strong>
                <div class="text-muted small"><?php echo clean(truncate_text($s['description'] ?? '', 90)); ?></div>
              </td>
              <td><?php echo date('M d, Y g:i A', strtotime($s['close_date'])); ?></td>
              <td><a href="survey_form.php?id=<?php echo (int)$s['survey_id']; ?>" class="btn btn-sm btn-coop">Answer Now</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
