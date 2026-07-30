<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

$total_surveys = $pdo->query("SELECT COUNT(*) AS c FROM surveys")->fetch()['c'];
$active_surveys = $pdo->query("
    SELECT COUNT(*) AS c FROM surveys
    WHERE status = 'active' AND NOW() BETWEEN open_date AND close_date
")->fetch()['c'];
$total_members = $pdo->query("SELECT COUNT(*) AS c FROM members WHERE status = 'active'")->fetch()['c'];
$total_responses = $pdo->query("SELECT COUNT(*) AS c FROM responses")->fetch()['c'];

$stmt = $pdo->prepare("SELECT first_login FROM staff WHERE staff_id = ?");
$stmt->execute([$_SESSION['staff_id']]);
$needs_password_change = (int)($stmt->fetch()['first_login'] ?? 0) === 1;

$recent = $pdo->query("
    SELECT r.submitted_at, m.full_name AS member_name, s.title AS survey_title
    FROM responses r
    JOIN members m ON m.member_id = r.member_id
    JOIN surveys s ON s.survey_id = r.survey_id
    ORDER BY r.submitted_at DESC
    LIMIT 8
")->fetchAll();

$surveys_summary = $pdo->query("
    SELECT s.survey_id, s.title, s.status, s.open_date, s.close_date,
           COUNT(r.response_id) AS response_count
    FROM surveys s
    LEFT JOIN responses r ON r.survey_id = s.survey_id
    GROUP BY s.survey_id
    ORDER BY s.created_at DESC
    LIMIT 5
")->fetchAll();

$page_title = 'Dashboard';
$active = 'dashboard';
require_once '../includes/header_staff.php';
?>

<?php if ($needs_password_change): ?>
  <div class="alert alert-warning alert-dismissible fade show d-flex justify-content-between align-items-center flex-wrap gap-2" role="alert">
    <div>
      This is your first login — you're still using your auto-generated default password.
      We recommend changing it now.
    </div>
    <div class="d-flex align-items-center gap-2">
      <a href="profile.php" class="btn btn-sm btn-dark">Change Password Now</a>
      <button type="button" class="btn-close" onclick="this.closest('.alert').remove()" aria-label="Remind me later">&times;</button>
    </div>
  </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-0">Welcome, <?php echo clean($_SESSION['staff_name']); ?></h4>
    <p class="text-muted mb-0">Cooperative Survey Management &mdash; Staff Overview</p>
  </div>
  <a href="survey_create.php" class="btn btn-dark">New Survey</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card stat-card bg-green p-3">
      <div class="stat-value"><?php echo (int)$total_surveys; ?></div>
      <div>Total Surveys</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card stat-card bg-gold p-3">
      <div class="stat-value"><?php echo (int)$active_surveys; ?></div>
      <div>Currently Active</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card stat-card bg-navy p-3">
      <div class="stat-value"><?php echo (int)$total_members; ?></div>
      <div>Active Members</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card stat-card bg-red p-3">
      <div class="stat-value"><?php echo (int)$total_responses; ?></div>
      <div>Total Responses</div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header bg-white fw-bold">Recent Surveys</div>
      <div class="card-body">
        <?php if (empty($surveys_summary)): ?>
          <p class="text-muted mb-0">No surveys created yet. <a href="survey_create.php">Create one</a>.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead><tr><th>Title</th><th>Status</th><th>Responses</th><th></th></tr></thead>
              <tbody>
              <?php foreach ($surveys_summary as $s): ?>
                <tr>
                  <td><?php echo clean($s['title']); ?></td>
                  <td><?php echo status_badge(get_effective_survey_status($s)); ?></td>
                  <td><?php echo (int)$s['response_count']; ?></td>
                  <td><a href="survey_results.php?id=<?php echo (int)$s['survey_id']; ?>" class="btn btn-sm btn-outline-dark">Results</a></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <a href="survey_list.php" class="small">View all surveys &rarr;</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header bg-white fw-bold">Recent Submissions</div>
      <div class="card-body">
        <?php if (empty($recent)): ?>
          <p class="text-muted mb-0">No responses submitted yet.</p>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($recent as $r): ?>
              <li class="list-group-item px-0">
                <strong><?php echo clean($r['member_name']); ?></strong> answered
                <em><?php echo clean($r['survey_title']); ?></em>
                <div class="text-muted small"><?php echo date('M d, Y g:i A', strtotime($r['submitted_at'])); ?></div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
