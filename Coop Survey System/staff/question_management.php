<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

$survey_id = (int)($_GET['survey_id'] ?? $_POST['survey_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM surveys WHERE survey_id = ?");
$stmt->execute([$survey_id]);
$survey = $stmt->fetch();

if (!$survey) {
    set_flash('error', 'Survey not found.');
    redirect('survey_list.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = trim($_POST['question_text'] ?? '');
    $type = in_array($_POST['question_type'] ?? '', ['multiple_choice','yes_no','rating','short_answer']) ? $_POST['question_type'] : 'short_answer';
    $required = !empty($_POST['is_required']) ? 1 : 0;
    $choicesRaw = trim($_POST['choices'] ?? '');

    if ($text === '') {
        $errors[] = 'Question text is required.';
    }
    if ($type === 'multiple_choice') {
        $choiceLines = array_filter(array_map('trim', explode("\n", $choicesRaw)));
        if (count($choiceLines) < 2) {
            $errors[] = 'Multiple choice questions need at least 2 choices (one per line).';
        }
    }

    if (empty($errors)) {
        $maxOrder = $pdo->prepare("SELECT COALESCE(MAX(question_order), -1) AS m FROM survey_questions WHERE survey_id = ?");
        $maxOrder->execute([$survey_id]);
        $nextOrder = $maxOrder->fetch()['m'] + 1;

        $stmt = $pdo->prepare("
            INSERT INTO survey_questions (survey_id, question_text, question_type, is_required, question_order)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$survey_id, $text, $type, $required, $nextOrder]);
        $question_id = $pdo->lastInsertId();

        if ($type === 'multiple_choice') {
            $cStmt = $pdo->prepare("INSERT INTO survey_choices (question_id, choice_text, choice_order) VALUES (?, ?, ?)");
            foreach ($choiceLines as $i => $c) {
                $cStmt->execute([$question_id, $c, $i]);
            }
        }

        set_flash('success', 'Question added.');
        redirect('question_management.php?survey_id=' . $survey_id);
    }
}

$stmt = $pdo->prepare("SELECT * FROM survey_questions WHERE survey_id = ? ORDER BY question_order, question_id");
$stmt->execute([$survey_id]);
$questions = $stmt->fetchAll();

$choicesByQuestion = [];
if ($questions) {
    $ids = array_column($questions, 'question_id');
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM survey_choices WHERE question_id IN ($in) ORDER BY choice_order, choice_id");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $c) {
        $choicesByQuestion[$c['question_id']][] = $c;
    }
}

$page_title = 'Question Management';
$active = 'surveys';
require_once '../includes/header_staff.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-0">Question Management</h4>
    <p class="text-muted mb-0">Survey: <strong><?php echo clean($survey['title']); ?></strong></p>
  </div>
  <a href="survey_list.php" class="btn btn-outline-secondary btn-sm">&larr; Back to Surveys</a>
</div>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header bg-white fw-bold">Add a Question</div>
      <div class="card-body">
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?php echo clean($e); ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>
        <form method="POST">
          <input type="hidden" name="survey_id" value="<?php echo (int)$survey_id; ?>">
          <div class="mb-3">
            <label class="form-label">Question Text</label>
            <textarea name="question_text" class="form-control" rows="2" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Type</label>
            <select name="question_type" class="form-select" id="qTypeSelect" onchange="document.getElementById('choicesField').style.display = this.value === 'multiple_choice' ? 'block' : 'none';">
              <option value="multiple_choice">Multiple Choice</option>
              <option value="yes_no">Yes / No</option>
              <option value="rating">Rating Scale (1-5)</option>
              <option value="short_answer">Short Answer</option>
            </select>
          </div>
          <div class="mb-3" id="choicesField">
            <label class="form-label">Choices (one per line)</label>
            <textarea name="choices" class="form-control" rows="4" placeholder="Excellent&#10;Good&#10;Fair&#10;Poor"></textarea>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_required" value="1" id="isRequired" checked>
            <label class="form-check-label" for="isRequired">Required</label>
          </div>
          <button type="submit" class="btn btn-coop w-100">Add Question</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card">
      <div class="card-header bg-white fw-bold">Existing Questions (<?php echo count($questions); ?>)</div>
      <div class="card-body">
        <?php if (empty($questions)): ?>
          <p class="text-muted mb-0">No questions added yet.</p>
        <?php else: ?>
          <?php foreach ($questions as $i => $q): ?>
            <div class="card question-block mb-2">
              <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <strong><?php echo ($i + 1) . '. ' . clean($q['question_text']); ?></strong>
                    <div class="text-muted small">
                      <?php echo question_type_label($q['question_type']); ?>
                      <?php echo $q['is_required'] ? ' &middot; Required' : ' &middot; Optional'; ?>
                    </div>
                    <?php if (!empty($choicesByQuestion[$q['question_id']])): ?>
                      <div class="small text-muted mt-1">
                        Choices: <?php echo clean(implode(', ', array_column($choicesByQuestion[$q['question_id']], 'choice_text'))); ?>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="text-nowrap">
                    <a href="question_edit.php?id=<?php echo (int)$q['question_id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form action="question_delete.php" method="POST" class="d-inline" data-confirm="Delete this question?">
                      <input type="hidden" name="question_id" value="<?php echo (int)$q['question_id']; ?>">
                      <input type="hidden" name="survey_id" value="<?php echo (int)$survey_id; ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
