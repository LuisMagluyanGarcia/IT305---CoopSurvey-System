<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $open_date = $_POST['open_date'] ?? '';
    $close_date = $_POST['close_date'] ?? '';
    $status = in_array($_POST['status'] ?? '', ['draft', 'active']) ? $_POST['status'] : 'draft';
    $questions = $_POST['questions'] ?? [];

    if ($title === '') $errors[] = 'Survey title is required.';
    if (!$open_date || !$close_date) $errors[] = 'Both open and close dates are required.';
    if ($open_date && $close_date && strtotime($close_date) <= strtotime($open_date)) {
        $errors[] = 'Close date must be after the open date.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO surveys (title, description, created_by, open_date, close_date, status)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $description, $_SESSION['staff_id'], $open_date, $close_date, $status]);
            $survey_id = $pdo->lastInsertId();

            $qStmt = $pdo->prepare("
                INSERT INTO survey_questions (survey_id, question_text, question_type, is_required, question_order)
                VALUES (?, ?, ?, ?, ?)
            ");
            $cStmt = $pdo->prepare("
                INSERT INTO survey_choices (question_id, choice_text, choice_order)
                VALUES (?, ?, ?)
            ");

            $order = 0;
            foreach ($questions as $q) {
                $text = trim($q['text'] ?? '');
                if ($text === '') continue;
                $type = in_array($q['type'] ?? '', ['multiple_choice','yes_no','rating','short_answer']) ? $q['type'] : 'short_answer';
                $required = !empty($q['required']) ? 1 : 0;

                $qStmt->execute([$survey_id, $text, $type, $required, $order]);
                $question_id = $pdo->lastInsertId();

                if ($type === 'multiple_choice' && !empty($q['choices'])) {
                    $cOrder = 0;
                    foreach ($q['choices'] as $choiceText) {
                        $choiceText = trim($choiceText);
                        if ($choiceText === '') continue;
                        $cStmt->execute([$question_id, $choiceText, $cOrder]);
                        $cOrder++;
                    }
                }
                $order++;
            }

            $pdo->commit();
            set_flash('success', 'Survey created successfully.');
            redirect('survey_list.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Could not create the survey. Please try again.';
        }
    }
}

$page_title = 'Create Survey';
$active = 'surveys';
require_once '../includes/header_staff.php';
?>

<h4 class="fw-bold mb-4">Create New Survey</h4>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $err): ?><li><?php echo clean($err); ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" novalidate>
  <div class="card mb-3">
    <div class="card-header bg-white fw-bold">Survey Details</div>
    <div class="card-body">
      <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required value="<?php echo isset($_POST['title']) ? clean($_POST['title']) : ''; ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2"><?php echo isset($_POST['description']) ? clean($_POST['description']) : ''; ?></textarea>
      </div>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Opens</label>
          <input type="datetime-local" name="open_date" class="form-control" required value="<?php echo isset($_POST['open_date']) ? clean($_POST['open_date']) : ''; ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Closes</label>
          <input type="datetime-local" name="close_date" class="form-control" required value="<?php echo isset($_POST['close_date']) ? clean($_POST['close_date']) : ''; ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Initial Status</label>
          <select name="status" class="form-select">
            <option value="draft">Draft (not visible to members)</option>
            <option value="active">Active (publish immediately)</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
      <span>Questions</span>
      <button type="button" id="addQuestionBtn" class="btn btn-sm btn-dark">Add Question</button>
    </div>
    <div class="card-body">
      <div id="questionsContainer"></div>
      <p class="text-muted small mb-0">You can also add or edit questions later from the Question Management page.</p>
    </div>
  </div>

  <button type="submit" class="btn btn-coop px-4">Save Survey</button>
  <a href="survey_list.php" class="btn btn-outline-secondary">Cancel</a>
</form>

<?php require_once '../includes/footer.php'; ?>
