<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

$question_id = (int)($_GET['id'] ?? $_POST['question_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM survey_questions WHERE question_id = ?");
$stmt->execute([$question_id]);
$question = $stmt->fetch();

if (!$question) {
    set_flash('error', 'Question not found.');
    redirect('survey_list.php');
}

$survey_id = $question['survey_id'];
$errors = [];

// Update question text/type/required
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_question'])) {
    $text = trim($_POST['question_text'] ?? '');
    $type = in_array($_POST['question_type'] ?? '', ['multiple_choice','yes_no','rating','short_answer']) ? $_POST['question_type'] : $question['question_type'];
    $required = !empty($_POST['is_required']) ? 1 : 0;

    if ($text === '') {
        $errors[] = 'Question text is required.';
    } else {
        $stmt = $pdo->prepare("UPDATE survey_questions SET question_text = ?, question_type = ?, is_required = ? WHERE question_id = ?");
        $stmt->execute([$text, $type, $required, $question_id]);
        set_flash('success', 'Question updated.');
        redirect('question_edit.php?id=' . $question_id);
    }
}

// Add a new choice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_choice'])) {
    $choiceText = trim($_POST['choice_text'] ?? '');
    if ($choiceText !== '') {
        $maxOrder = $pdo->prepare("SELECT COALESCE(MAX(choice_order), -1) AS m FROM survey_choices WHERE question_id = ?");
        $maxOrder->execute([$question_id]);
        $nextOrder = $maxOrder->fetch()['m'] + 1;
        $stmt = $pdo->prepare("INSERT INTO survey_choices (question_id, choice_text, choice_order) VALUES (?, ?, ?)");
        $stmt->execute([$question_id, $choiceText, $nextOrder]);
        set_flash('success', 'Choice added.');
    }
    redirect('question_edit.php?id=' . $question_id);
}

$stmt = $pdo->prepare("SELECT * FROM survey_choices WHERE question_id = ? ORDER BY choice_order, choice_id");
$stmt->execute([$question_id]);
$choices = $stmt->fetchAll();

$page_title = 'Edit Question';
$active = 'surveys';
require_once '../includes/header_staff.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0">Edit Question</h4>
  <a href="question_management.php?survey_id=<?php echo (int)$survey_id; ?>" class="btn btn-outline-secondary btn-sm">&larr; Back</a>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?php echo clean($e); ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header bg-white fw-bold">Question Details</div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="update_question" value="1">
          <div class="mb-3">
            <label class="form-label">Question Text</label>
            <textarea name="question_text" class="form-control" rows="2" required><?php echo clean($question['question_text']); ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Type</label>
            <select name="question_type" class="form-select">
              <?php foreach (['multiple_choice'=>'Multiple Choice','yes_no'=>'Yes / No','rating'=>'Rating Scale (1-5)','short_answer'=>'Short Answer'] as $val => $label): ?>
                <option value="<?php echo $val; ?>" <?php echo $question['question_type'] === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_required" value="1" id="isRequired" <?php echo $question['is_required'] ? 'checked' : ''; ?>>
            <label class="form-check-label" for="isRequired">Required</label>
          </div>
          <button type="submit" class="btn btn-coop">Save Changes</button>
        </form>
      </div>
    </div>
  </div>

  <?php if ($question['question_type'] === 'multiple_choice'): ?>
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header bg-white fw-bold">Choices</div>
      <div class="card-body">
        <?php if (empty($choices)): ?>
          <p class="text-muted">No choices yet.</p>
        <?php else: ?>
          <ul class="list-group mb-3">
            <?php foreach ($choices as $c): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php echo clean($c['choice_text']); ?>
                <form action="choice_delete.php" method="POST" data-confirm="Remove this choice?">
                  <input type="hidden" name="choice_id" value="<?php echo (int)$c['choice_id']; ?>">
                  <input type="hidden" name="question_id" value="<?php echo (int)$question_id; ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <form method="POST" class="d-flex gap-2">
          <input type="hidden" name="add_choice" value="1">
          <input type="text" name="choice_text" class="form-control" placeholder="New choice text" required>
          <button type="submit" class="btn btn-dark">Add</button>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
