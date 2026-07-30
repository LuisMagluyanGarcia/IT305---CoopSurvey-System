<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

$survey_id = (int)($_GET['survey_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM surveys WHERE survey_id = ?");
$stmt->execute([$survey_id]);
$survey = $stmt->fetch();

if (!$survey) {
    set_flash('error', 'Survey not found.');
    redirect('survey_list.php');
}

$stmt = $pdo->prepare("
    SELECT m.member_id, m.account_number, m.full_name, r.submitted_at
    FROM responses r
    JOIN members m ON m.member_id = r.member_id
    WHERE r.survey_id = ?
    ORDER BY r.submitted_at DESC
");
$stmt->execute([$survey_id]);
$respondents = $stmt->fetchAll();

$total_members = $pdo->query("SELECT COUNT(*) AS c FROM members WHERE status = 'active'")->fetch()['c'];
$participation_rate = $total_members > 0 ? round((count($respondents) / $total_members) * 100, 1) : 0;

$page_title = 'Respondents';
$active = 'surveys';
require_once '../includes/header_staff.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-0">Respondents</h4>
    <p class="text-muted mb-0">Survey: <strong><?php echo clean($survey['title']); ?></strong></p>
  </div>
  <a href="survey_list.php" class="btn btn-outline-secondary btn-sm">&larr; Back to Surveys</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card stat-card bg-green p-3">
      <div class="stat-value"><?php echo count($respondents); ?></div>
      <div>Total Respondents</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card bg-gold p-3">
      <div class="stat-value"><?php echo (int)$total_members; ?></div>
      <div>Active Members (eligible)</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card bg-navy p-3">
      <div class="stat-value"><?php echo $participation_rate; ?>%</div>
      <div>Participation Rate</div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header bg-white fw-bold">Members Who Responded</div>
  <div class="card-body">
    <?php if (empty($respondents)): ?>
      <p class="text-muted mb-0">No one has responded to this survey yet.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>#</th><th>Account Number</th><th>Name</th><th>Submitted At</th></tr></thead>
          <tbody>
          <?php foreach ($respondents as $i => $r): ?>
            <tr>
              <td><?php echo $i + 1; ?></td>
              <td><?php echo clean($r['account_number']); ?></td>
              <td><?php echo clean($r['full_name']); ?></td>
              <td><?php echo date('M d, Y g:i A', strtotime($r['submitted_at'])); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
