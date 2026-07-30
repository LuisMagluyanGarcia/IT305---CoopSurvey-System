<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_member_login();

$survey_id = (int)($_GET['survey_id'] ?? 0);
$member_id = $_SESSION['member_id'];

$stmt = $pdo->prepare("
    SELECT s.title, r.submitted_at
    FROM responses r
    JOIN surveys s ON s.survey_id = r.survey_id
    WHERE r.survey_id = ? AND r.member_id = ?
");
$stmt->execute([$survey_id, $member_id]);
$row = $stmt->fetch();

if (!$row) {
    redirect('available_surveys.php');
}

$page_title = 'Submission Confirmed';
$active = 'surveys';
require_once '../includes/header_member.php';
?>

<div class="d-flex justify-content-center">
  <div class="card text-center p-4" style="max-width:520px;">
    <div class="card-body">
      <div class="success-mark">&#10003;</div>
      <h4 class="fw-bold mt-3">Response Submitted!</h4>
      <p class="text-muted">
        Thank you for answering <strong><?php echo clean($row['title']); ?></strong>.
        Your response was recorded on <?php echo date('M d, Y g:i A', strtotime($row['submitted_at'])); ?>.
      </p>
      <div class="d-grid gap-2 mt-3">
        <a href="available_surveys.php" class="btn btn-coop">Back to Surveys</a>
        <a href="dashboard.php" class="btn btn-outline-secondary">Go to Dashboard</a>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
