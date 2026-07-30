<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

$surveys = $pdo->query("
    SELECT s.survey_id, s.title, s.status, s.open_date, s.close_date,
           COUNT(r.response_id) AS response_count
    FROM surveys s
    LEFT JOIN responses r ON r.survey_id = s.survey_id
    GROUP BY s.survey_id
    ORDER BY s.created_at DESC
")->fetchAll();

$page_title = 'Reports';
$active = 'reports';
require_once '../includes/header_staff.php';
?>

<h4 class="fw-bold mb-4">Reports</h4>

<div class="card">
  <div class="card-body">
    <?php if (empty($surveys)): ?>
      <p class="text-muted mb-0">No surveys available to report on yet.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>Survey</th><th>Status</th><th>Responses</th><th style="width:260px;"></th></tr></thead>
          <tbody>
          <?php foreach ($surveys as $s): ?>
            <tr>
              <td><?php echo clean($s['title']); ?></td>
              <td><?php echo status_badge(get_effective_survey_status($s)); ?></td>
              <td><?php echo (int)$s['response_count']; ?></td>
              <td class="text-nowrap">
                <a href="report_view.php?id=<?php echo (int)$s['survey_id']; ?>" class="btn btn-sm btn-outline-dark">Printable Report</a>
                <a href="export_csv.php?id=<?php echo (int)$s['survey_id']; ?>" class="btn btn-sm btn-outline-success">Export CSV</a>
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
