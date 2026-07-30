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

$chartLabels = array_map(fn($s) => $s['title'], $surveys);
$chartData = array_map(fn($s) => (int)$s['response_count'], $surveys);

$page_title = 'Results Dashboard';
$active = 'results';
require_once '../includes/header_staff.php';
?>

<h4 class="fw-bold mb-4">Results Dashboard</h4>

<div class="card mb-4">
  <div class="card-header bg-white fw-bold">Responses per Survey</div>
  <div class="card-body">
    <?php if (empty($surveys)): ?>
      <p class="text-muted mb-0">No surveys yet.</p>
    <?php else: ?>
      <canvas id="responsesChart" height="90"></canvas>
    <?php endif; ?>
  </div>
</div>

<div class="card">
  <div class="card-header bg-white fw-bold">All Surveys</div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th>Title</th><th>Status</th><th>Responses</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($surveys as $s): ?>
          <tr>
            <td><?php echo clean($s['title']); ?></td>
            <td><?php echo status_badge(get_effective_survey_status($s)); ?></td>
            <td><?php echo (int)$s['response_count']; ?></td>
            <td><a href="survey_results.php?id=<?php echo (int)$s['survey_id']; ?>" class="btn btn-sm btn-outline-success">View Details</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
<?php if (!empty($surveys)): ?>
new Chart(document.getElementById('responsesChart'), {
  type: 'bar',
  data: {
    labels: <?php echo json_encode($chartLabels); ?>,
    datasets: [{
      label: 'Responses',
      data: <?php echo json_encode($chartData); ?>,
      backgroundColor: '#1e6b3e'
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
  }
});
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>
