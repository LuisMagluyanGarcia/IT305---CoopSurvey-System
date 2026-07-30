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

$stmt = $pdo->prepare("SELECT * FROM survey_questions WHERE survey_id = ? ORDER BY question_order, question_id");
$stmt->execute([$survey_id]);
$questions = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT r.response_id, r.submitted_at, m.account_number, m.full_name
    FROM responses r JOIN members m ON m.member_id = r.member_id
    WHERE r.survey_id = ? ORDER BY r.submitted_at
");
$stmt->execute([$survey_id]);
$responses = $stmt->fetchAll();

// Preload all answers grouped by response_id -> question_id
$answersByResponse = [];
if ($responses) {
    $rids = array_column($responses, 'response_id');
    $in = implode(',', array_fill(0, count($rids), '?'));
    $stmt = $pdo->prepare("
        SELECT ra.response_id, ra.question_id, ra.rating_value, ra.answer_text, c.choice_text
        FROM response_answers ra
        LEFT JOIN survey_choices c ON c.choice_id = ra.choice_id
        WHERE ra.response_id IN ($in)
    ");
    $stmt->execute($rids);
    foreach ($stmt->fetchAll() as $a) {
        $value = $a['choice_text'] ?? ($a['rating_value'] !== null ? $a['rating_value'] : $a['answer_text']);
        $answersByResponse[$a['response_id']][$a['question_id']] = $value;
    }
}

$filename = 'survey_' . $survey_id . '_responses_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

$header = ['Account Number', 'Member Name', 'Submitted At'];
foreach ($questions as $q) {
    $header[] = $q['question_text'];
}
fputcsv($out, $header);

foreach ($responses as $r) {
    $row = [$r['account_number'], $r['full_name'], $r['submitted_at']];
    foreach ($questions as $q) {
        $row[] = $answersByResponse[$r['response_id']][$q['question_id']] ?? '';
    }
    fputcsv($out, $row);
}

fclose($out);
exit;
