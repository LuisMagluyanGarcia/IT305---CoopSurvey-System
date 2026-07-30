<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_id = (int)($_POST['question_id'] ?? 0);
    $survey_id = (int)($_POST['survey_id'] ?? 0);
    if ($question_id) {
        $stmt = $pdo->prepare("DELETE FROM survey_questions WHERE question_id = ?");
        $stmt->execute([$question_id]);
        set_flash('success', 'Question deleted.');
    }
    redirect('question_management.php?survey_id=' . $survey_id);
}
redirect('survey_list.php');
