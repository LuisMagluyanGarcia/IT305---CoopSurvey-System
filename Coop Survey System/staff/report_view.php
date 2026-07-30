<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

$survey_id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM surveys WHERE survey_id = ?");
$stmt->execute([$survey_id]);
$survey = $stmt->fetch();

if (!$survey) {
    set_flash('error', 'Survey not found.');
    redirect('survey_list.php');
}

$stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM responses WHERE survey_id = ?");
$stmt->execute([$survey_id]);
$total_responses = $stmt->fetch()['c'];

$stmt = $pdo->prepare("SELECT * FROM survey_questions WHERE survey_id = ? ORDER BY question_order, question_id");
$stmt->execute([$survey_id]);
$questions = $stmt->fetchAll();

$results = [];
foreach ($questions as $q) {
    $qid = $q['question_id'];
    if ($q['question_type'] === 'multiple_choice') {
        $stmt = $pdo->prepare("
            SELECT c.choice_text, COUNT(a.answer_id) AS cnt
            FROM survey_choices c
            LEFT JOIN response_answers a ON a.choice_id = c.choice_id
            WHERE c.question_id = ? GROUP BY c.choice_id ORDER BY c.choice_order
        ");
        $stmt->execute([$qid]);
        $results[$qid] = ['type' => 'choice', 'rows' => $stmt->fetchAll()];
    } elseif ($q['question_type'] === 'yes_no') {
        $stmt = $pdo->prepare("SELECT answer_text AS label, COUNT(*) AS cnt FROM response_answers WHERE question_id = ? AND answer_text IN ('Yes','No') GROUP BY answer_text");
        $stmt->execute([$qid]);
        $yes = 0; $no = 0;
        foreach ($stmt->fetchAll() as $r) {
            if ($r['label'] === 'Yes') $yes = (int)$r['cnt'];
            if ($r['label'] === 'No') $no = (int)$r['cnt'];
        }
        $results[$qid] = ['type' => 'yesno', 'yes' => $yes, 'no' => $no];
    } elseif ($q['question_type'] === 'rating') {
        $stmt = $pdo->prepare("SELECT AVG(rating_value) AS avg_val, COUNT(*) AS c FROM response_answers WHERE question_id = ? AND rating_value IS NOT NULL");
        $stmt->execute([$qid]);
        $row = $stmt->fetch();
        $results[$qid] = ['type' => 'rating', 'avg' => round($row['avg_val'] ?? 0, 2), 'count' => (int)$row['c']];
    } elseif ($q['question_type'] === 'short_answer') {
        $stmt = $pdo->prepare("SELECT answer_text FROM response_answers WHERE question_id = ? AND answer_text IS NOT NULL AND answer_text != ''");
        $stmt->execute([$qid]);
        $results[$qid] = ['type' => 'text', 'rows' => $stmt->fetchAll()];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Report - <?php echo clean($survey['title']); ?></title>
<link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="staff-theme">
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <a href="survey_results.php?id=<?php echo (int)$survey_id; ?>" class="btn btn-outline-secondary btn-sm">&larr; Back to Results</a>
    <button onclick="window.print()" class="btn btn-dark btn-sm">Print / Save as PDF</button>
  </div>

  <div class="text-center mb-4">
    <h4 class="fw-bold mb-0">Multipurpose Cooperative</h4>
    <p class="text-muted mb-0">Survey Report</p>
  </div>

  <table class="table table-sm mb-4">
    <tr><th style="width:200px;">Survey Title</th><td><?php echo clean($survey['title']); ?></td></tr>
    <tr><th>Description</th><td><?php echo clean($survey['description']); ?></td></tr>
    <tr><th>Open Date</th><td><?php echo date('M d, Y g:i A', strtotime($survey['open_date'])); ?></td></tr>
    <tr><th>Close Date</th><td><?php echo date('M d, Y g:i A', strtotime($survey['close_date'])); ?></td></tr>
    <tr><th>Total Responses</th><td><?php echo (int)$total_responses; ?></td></tr>
    <tr><th>Report Generated</th><td><?php echo date('M d, Y g:i A'); ?></td></tr>
  </table>

  <?php foreach ($questions as $i => $q): $qid = $q['question_id']; $r = $results[$qid]; ?>
    <h6 class="fw-bold mt-4"><?php echo ($i + 1) . '. ' . clean($q['question_text']); ?></h6>
    <?php if ($r['type'] === 'choice'): ?>
      <table class="table table-sm table-bordered">
        <thead><tr><th>Choice</th><th>Responses</th></tr></thead>
        <tbody>
        <?php foreach ($r['rows'] as $row): ?>
          <tr><td><?php echo clean($row['choice_text']); ?></td><td><?php echo (int)$row['cnt']; ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php elseif ($r['type'] === 'yesno'): ?>
      <p>Yes: <?php echo $r['yes']; ?> &nbsp; No: <?php echo $r['no']; ?></p>
    <?php elseif ($r['type'] === 'rating'): ?>
      <p>Average rating: <strong><?php echo $r['avg']; ?> / 5</strong> (from <?php echo $r['count']; ?> responses)</p>
    <?php elseif ($r['type'] === 'text'): ?>
      <ul>
        <?php foreach ($r['rows'] as $row): ?>
          <li><?php echo clean($row['answer_text']); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  <?php endforeach; ?>

</main>
</body>
</html>
