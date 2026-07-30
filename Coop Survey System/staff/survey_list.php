<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

$surveys = $pdo->query("
    SELECT s.*, COUNT(r.response_id) AS response_count
    FROM surveys s
    LEFT JOIN responses r ON r.survey_id = s.survey_id
    GROUP BY s.survey_id
    ORDER BY s.created_at DESC
")->fetchAll();

$page_title = 'Survey Management';
$active = 'surveys';
require_once '../includes/header_staff.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0">Survey Management</h4>
  <a href="survey_create.php" class="btn btn-dark">New Survey</a>
</div>

<div class="card">
  <div class="card-body">
    <?php if (empty($surveys)): ?>
      <p class="text-muted mb-0">No surveys yet. Click "New Survey" to create the first one.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Title</th>
              <th>Open</th>
              <th>Close</th>
              <th>Status</th>
              <th>Responses</th>
              <th style="width:280px;"></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($surveys as $s): ?>
            <tr>
              <td><strong><?php echo clean($s['title']); ?></strong></td>
              <td><?php echo date('M d, Y', strtotime($s['open_date'])); ?></td>
              <td><?php echo date('M d, Y', strtotime($s['close_date'])); ?></td>
              <td><?php echo status_badge(get_effective_survey_status($s)); ?></td>
              <td><?php echo (int)$s['response_count']; ?></td>
              <td class="text-nowrap">
                <a href="survey_edit.php?id=<?php echo (int)$s['survey_id']; ?>" class="btn btn-sm btn-outline-secondary" title="Edit details">Edit</a>
                <a href="question_management.php?survey_id=<?php echo (int)$s['survey_id']; ?>" class="btn btn-sm btn-outline-primary" title="Manage questions">Questions</a>
                <a href="respondents.php?survey_id=<?php echo (int)$s['survey_id']; ?>" class="btn btn-sm btn-outline-info" title="Respondents">Respondents</a>
                <a href="survey_results.php?id=<?php echo (int)$s['survey_id']; ?>" class="btn btn-sm btn-outline-success" title="Results">Results</a>

                <?php if ($s['status'] === 'active'): ?>
                  <form action="survey_status.php" method="POST" class="d-inline" data-confirm="Deactivate this survey?">
                    <input type="hidden" name="survey_id" value="<?php echo (int)$s['survey_id']; ?>">
                    <input type="hidden" name="new_status" value="closed">
                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Deactivate">Deactivate</button>
                  </form>
                <?php else: ?>
                  <form action="survey_status.php" method="POST" class="d-inline">
                    <input type="hidden" name="survey_id" value="<?php echo (int)$s['survey_id']; ?>">
                    <input type="hidden" name="new_status" value="active">
                    <button type="submit" class="btn btn-sm btn-outline-success" title="Activate">Activate</button>
                  </form>
                <?php endif; ?>

                <form action="survey_delete.php" method="POST" class="d-inline" data-confirm="Delete this survey permanently? This will remove all its questions and responses.">
                  <input type="hidden" name="survey_id" value="<?php echo (int)$s['survey_id']; ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">Delete</button>
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
