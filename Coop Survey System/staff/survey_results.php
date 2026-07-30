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

$results = []; // question_id => computed stats
$chartPayloads = [];

foreach ($questions as $q) {
    $qid = $q['question_id'];

    if ($q['question_type'] === 'multiple_choice') {
        $stmt = $pdo->prepare("
            SELECT c.choice_text, COUNT(a.answer_id) AS cnt
            FROM survey_choices c
            LEFT JOIN response_answers a ON a.choice_id = c.choice_id
            WHERE c.question_id = ?
            GROUP BY c.choice_id
            ORDER BY c.choice_order
        ");
        $stmt->execute([$qid]);
        $rows = $stmt->fetchAll();
        $results[$qid] = ['type' => 'choice', 'rows' => $rows];
        $chartPayloads[$qid] = [
            'labels' => array_column($rows, 'choice_text'),
            'data' => array_map('intval', array_column($rows, 'cnt')),
        ];

    } elseif ($q['question_type'] === 'yes_no') {
        $stmt = $pdo->prepare("
            SELECT answer_text AS label, COUNT(*) AS cnt
            FROM response_answers
            WHERE question_id = ? AND answer_text IN ('Yes','No')
            GROUP BY answer_text
        ");
        $stmt->execute([$qid]);
        $rows = $stmt->fetchAll();
        $yes = 0; $no = 0;
        foreach ($rows as $r) {
            if ($r['label'] === 'Yes') $yes = (int)$r['cnt'];
            if ($r['label'] === 'No') $no = (int)$r['cnt'];
        }
        $results[$qid] = ['type' => 'yesno', 'yes' => $yes, 'no' => $no];
        $chartPayloads[$qid] = ['labels' => ['Yes', 'No'], 'data' => [$yes, $no]];

    } elseif ($q['question_type'] === 'rating') {
        $stmt = $pdo->prepare("
            SELECT rating_value, COUNT(*) AS cnt
            FROM response_answers
            WHERE question_id = ? AND rating_value IS NOT NULL
            GROUP BY rating_value
        ");
        $stmt->execute([$qid]);
        $rows = $stmt->fetchAll();
        $dist = array_fill(1, 5, 0);
        $sum = 0; $count = 0;
        foreach ($rows as $r) {
            $dist[(int)$r['rating_value']] = (int)$r['cnt'];
            $sum += $r['rating_value'] * $r['cnt'];
            $count += $r['cnt'];
        }
        $avg = $count > 0 ? round($sum / $count, 2) : 0;
        $results[$qid] = ['type' => 'rating', 'avg' => $avg, 'count' => $count, 'dist' => $dist];
        $chartPayloads[$qid] = ['labels' => ['1','2','3','4','5'], 'data' => array_values($dist)];

    } elseif ($q['question_type'] === 'short_answer') {
        $stmt = $pdo->prepare("
            SELECT ra.answer_text, m.full_name
            FROM response_answers ra
            JOIN responses r ON r.response_id = ra.response_id
            JOIN members m ON m.member_id = r.member_id
            WHERE ra.question_id = ? AND ra.answer_text IS NOT NULL AND ra.answer_text != ''
            ORDER BY r.submitted_at DESC
        ");
        $stmt->execute([$qid]);
        $results[$qid] = ['type' => 'text', 'rows' => $stmt->fetchAll()];
    }
}

$page_title = 'Survey Results';
$active = 'results';
require_once '../includes/header_staff.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-0"><?php echo clean($survey['title']); ?></h4>
    <p class="text-muted mb-0"><?php echo (int)$total_responses; ?> total response(s) &middot; <?php echo status_badge(get_effective_survey_status($survey)); ?></p>
  </div>
  <div class="no-print">
    <a href="report_view.php?id=<?php echo (int)$survey_id; ?>" class="btn btn-outline-dark btn-sm">Printable Report</a>
    <a href="export_csv.php?id=<?php echo (int)$survey_id; ?>" class="btn btn-outline-success btn-sm">Export CSV</a>
    <a href="survey_list.php" class="btn btn-outline-secondary btn-sm">&larr; Back</a>
  </div>
</div>

<?php if (empty($questions)): ?>
  <p class="text-muted">This survey has no questions.</p>
<?php elseif ($total_responses == 0): ?>
  <div class="alert alert-info">No responses have been submitted yet for this survey.</div>
<?php endif; ?>

<?php foreach ($questions as $i => $q): $qid = $q['question_id']; $r = $results[$qid]; ?>
  <div class="card mb-3">
    <div class="card-header bg-white fw-bold"><?php echo ($i + 1) . '. ' . clean($q['question_text']); ?></div>
    <div class="card-body">

      <?php if ($r['type'] === 'choice' || $r['type'] === 'yesno'): ?>
        <canvas id="chart<?php echo $qid; ?>" height="70"></canvas>
        <?php if ($r['type'] === 'choice'): ?>
          <table class="table table-sm mt-3">
            <thead><tr><th>Choice</th><th>Responses</th></tr></thead>
            <tbody>
            <?php foreach ($r['rows'] as $row): ?>
              <tr><td><?php echo clean($row['choice_text']); ?></td><td><?php echo (int)$row['cnt']; ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="mt-3 mb-0"><strong>Yes:</strong> <?php echo $r['yes']; ?> &nbsp; <strong>No:</strong> <?php echo $r['no']; ?></p>
        <?php endif; ?>

      <?php elseif ($r['type'] === 'rating'): ?>
        <div class="row">
          <div class="col-md-6">
            <canvas id="chart<?php echo $qid; ?>" height="80"></canvas>
          </div>
          <div class="col-md-6 d-flex flex-column justify-content-center">
            <div class="text-center">
              <div class="display-4 fw-bold text-success"><?php echo $r['avg']; ?> / 5</div>
              <div class="text-muted">Average rating from <?php echo $r['count']; ?> response(s)</div>
            </div>
          </div>
        </div>

      <?php elseif ($r['type'] === 'text'): ?>
        <?php if (empty($r['rows'])): ?>
          <p class="text-muted mb-0">No answers submitted yet.</p>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($r['rows'] as $row): ?>
              <li class="list-group-item px-0">
                <div><?php echo nl2br(clean($row['answer_text'])); ?></div>
                <div class="text-muted small">&mdash; <?php echo clean($row['full_name']); ?></div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      <?php endif; ?>

    </div>
  </div>
<?php endforeach; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
<?php foreach ($chartPayloads as $qid => $payload): ?>
new Chart(document.getElementById('chart<?php echo $qid; ?>'), {
  type: '<?php echo $results[$qid]['type'] === 'rating' ? 'bar' : 'pie'; ?>',
  data: {
    labels: <?php echo json_encode($payload['labels']); ?>,
    datasets: [{
      label: 'Responses',
      data: <?php echo json_encode($payload['data']); ?>,
      backgroundColor: ['#1e6b3e','#d9a441','#2f8f57','#8f2f2f','#454b5c','#3b6ea5']
    }]
  },
  options: { responsive: true, plugins: { legend: { display: <?php echo $results[$qid]['type'] === 'rating' ? 'false' : 'true'; ?> } } }
});
<?php endforeach; ?>
</script>

<?php require_once '../includes/footer.php'; ?>
