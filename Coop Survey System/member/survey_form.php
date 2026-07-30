<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_member_login();

$member_id = $_SESSION['member_id'];
$survey_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM surveys WHERE survey_id = ?");
$stmt->execute([$survey_id]);
$survey = $stmt->fetch();

if (!$survey) {
    set_flash('error', 'Survey not found.');
    redirect('available_surveys.php');
}

if (get_effective_survey_status($survey) !== 'active') {
    set_flash('error', 'This survey is not currently open for responses.');
    redirect('available_surveys.php');
}

$stmt = $pdo->prepare("SELECT response_id FROM responses WHERE survey_id = ? AND member_id = ?");
$stmt->execute([$survey_id, $member_id]);
if ($stmt->fetch()) {
    set_flash('info', 'You have already answered this survey.');
    redirect('available_surveys.php');
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

$page_title = clean($survey['title']);
$active = 'surveys';
require_once '../includes/header_member.php';
?>

<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <h4 class="fw-bold mb-1"><?php echo clean($survey['title']); ?></h4>
    <p class="text-muted mb-0"><?php echo nl2br(clean($survey['description'])); ?></p>
  </div>
  <span class="text-muted small">Closes <?php echo date('M d, Y g:i A', strtotime($survey['close_date'])); ?></span>
</div>

<form method="POST" action="submit_survey.php" id="surveyResponseForm">
  <input type="hidden" name="survey_id" value="<?php echo (int)$survey_id; ?>">

  <?php if (empty($questions)): ?>
    <div class="alert alert-warning">This survey has no questions yet.</div>
  <?php endif; ?>

  <?php foreach ($questions as $i => $q): ?>
    <div class="card question-block mb-3" data-required="<?php echo (int)$q['is_required']; ?>" data-type="<?php echo clean($q['question_type']); ?>">
      <div class="card-body">
        <label class="form-label fw-semibold">
          <?php echo ($i + 1) . '. ' . clean($q['question_text']); ?>
          <?php if ($q['is_required']): ?><span class="text-danger">*</span><?php endif; ?>
        </label>

        <?php if ($q['question_type'] === 'multiple_choice'): ?>
          <?php foreach (($choicesByQuestion[$q['question_id']] ?? []) as $c): ?>
            <div class="form-check">
              <input class="form-check-input" type="radio"
                     name="answers[<?php echo (int)$q['question_id']; ?>][choice_id]"
                     value="<?php echo (int)$c['choice_id']; ?>"
                     id="c<?php echo (int)$c['choice_id']; ?>">
              <label class="form-check-label" for="c<?php echo (int)$c['choice_id']; ?>"><?php echo clean($c['choice_text']); ?></label>
            </div>
          <?php endforeach; ?>

        <?php elseif ($q['question_type'] === 'yes_no'): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="answers[<?php echo (int)$q['question_id']; ?>][text]" value="Yes" id="yes<?php echo (int)$q['question_id']; ?>">
            <label class="form-check-label" for="yes<?php echo (int)$q['question_id']; ?>">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="answers[<?php echo (int)$q['question_id']; ?>][text]" value="No" id="no<?php echo (int)$q['question_id']; ?>">
            <label class="form-check-label" for="no<?php echo (int)$q['question_id']; ?>">No</label>
          </div>

        <?php elseif ($q['question_type'] === 'rating'): ?>
          <div class="rating-widget">
            <input type="hidden" name="answers[<?php echo (int)$q['question_id']; ?>][rating]" value="">
            <?php for ($v = 1; $v <= 5; $v++): ?>
              <span class="rating-star" data-value="<?php echo $v; ?>">&#9733;</span>
            <?php endfor; ?>
          </div>

        <?php elseif ($q['question_type'] === 'short_answer'): ?>
          <textarea class="form-control" rows="3" name="answers[<?php echo (int)$q['question_id']; ?>][text]" placeholder="Type your answer here..."></textarea>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (!empty($questions)): ?>
    <button type="submit" class="btn btn-coop px-4">Submit Response</button>
    <a href="available_surveys.php" class="btn btn-outline-secondary">Cancel</a>
  <?php else: ?>
    <a href="available_surveys.php" class="btn btn-outline-secondary">Back</a>
  <?php endif; ?>
</form>

<?php require_once '../includes/footer.php'; ?>
