<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

$survey_id = (int)($_GET['id'] ?? $_POST['survey_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM surveys WHERE survey_id = ?");
$stmt->execute([$survey_id]);
$survey = $stmt->fetch();

if (!$survey) {
    set_flash('error', 'Survey not found.');
    redirect('survey_list.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $open_date = $_POST['open_date'] ?? '';
    $close_date = $_POST['close_date'] ?? '';
    $status = in_array($_POST['status'] ?? '', ['draft','active','closed']) ? $_POST['status'] : $survey['status'];

    if ($title === '') $errors[] = 'Survey title is required.';
    if (!$open_date || !$close_date) $errors[] = 'Both open and close dates are required.';
    if ($open_date && $close_date && strtotime($close_date) <= strtotime($open_date)) {
        $errors[] = 'Close date must be after the open date.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE surveys SET title = ?, description = ?, open_date = ?, close_date = ?, status = ?
            WHERE survey_id = ?
        ");
        $stmt->execute([$title, $description, $open_date, $close_date, $status, $survey_id]);
        set_flash('success', 'Survey updated successfully.');
        redirect('survey_list.php');
    }
    $survey = array_merge($survey, compact('title', 'description', 'open_date', 'close_date', 'status'));
}

$page_title = 'Edit Survey';
$active = 'surveys';
require_once '../includes/header_staff.php';

function dt_local($value) { return date('Y-m-d\TH:i', strtotime($value)); }
?>

<h4 class="fw-bold mb-4">Edit Survey</h4>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $err): ?><li><?php echo clean($err); ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST">
  <input type="hidden" name="survey_id" value="<?php echo (int)$survey_id; ?>">
  <div class="card mb-3">
    <div class="card-body">
      <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required value="<?php echo clean($survey['title']); ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2"><?php echo clean($survey['description']); ?></textarea>
      </div>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Opens</label>
          <input type="datetime-local" name="open_date" class="form-control" required value="<?php echo dt_local($survey['open_date']); ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Closes</label>
          <input type="datetime-local" name="close_date" class="form-control" required value="<?php echo dt_local($survey['close_date']); ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="draft" <?php echo $survey['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
            <option value="active" <?php echo $survey['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="closed" <?php echo $survey['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
          </select>
        </div>
      </div>
    </div>
  </div>
  <button type="submit" class="btn btn-coop px-4">Save Changes</button>
  <a href="question_management.php?survey_id=<?php echo (int)$survey_id; ?>" class="btn btn-outline-primary">Manage Questions</a>
  <a href="survey_list.php" class="btn btn-outline-secondary">Cancel</a>
</form>

<?php require_once '../includes/footer.php'; ?>
