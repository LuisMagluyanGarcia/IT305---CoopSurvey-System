<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_member_login();

$member_id = $_SESSION['member_id'];

$stmt = $pdo->prepare("
    SELECT s.*, r.response_id, r.submitted_at
    FROM surveys s
    LEFT JOIN responses r ON r.survey_id = s.survey_id AND r.member_id = ?
    WHERE s.status != 'draft'
    ORDER BY s.open_date DESC
");
$stmt->execute([$member_id]);
$surveys = $stmt->fetchAll();

$page_title = 'Available Surveys';
$active = 'surveys';
require_once '../includes/header_member.php';
?>

<h4 class="fw-bold mb-4">Surveys</h4>

<div class="card">
  <div class="card-body">
    <?php if (empty($surveys)): ?>
      <p class="text-muted mb-0">No surveys have been published yet.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Title</th>
              <th>Open</th>
              <th>Close</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($surveys as $s):
              $effective = get_effective_survey_status($s);
              $answered = !empty($s['response_id']);
          ?>
            <tr>
              <td>
                <strong><?php echo clean($s['title']); ?></strong>
                <div class="text-muted small"><?php echo clean(truncate_text($s['description'] ?? '', 100)); ?></div>
              </td>
              <td><?php echo date('M d, Y', strtotime($s['open_date'])); ?></td>
              <td><?php echo date('M d, Y', strtotime($s['close_date'])); ?></td>
              <td>
                <?php if ($answered): ?>
                  <span class="badge text-bg-primary">Completed</span>
                <?php else: ?>
                  <?php echo status_badge($effective); ?>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($answered): ?>
                  <span class="text-muted small">Submitted <?php echo date('M d, Y', strtotime($s['submitted_at'])); ?></span>
                <?php elseif ($effective === 'active'): ?>
                  <a href="survey_form.php?id=<?php echo (int)$s['survey_id']; ?>" class="btn btn-sm btn-coop">Answer Now</a>
                <?php elseif ($effective === 'upcoming'): ?>
                  <span class="text-muted small">Opens <?php echo date('M d, Y', strtotime($s['open_date'])); ?></span>
                <?php else: ?>
                  <span class="text-muted small">Survey closed</span>
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
