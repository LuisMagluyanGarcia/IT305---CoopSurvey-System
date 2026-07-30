<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $choice_id = (int)($_POST['choice_id'] ?? 0);
    $question_id = (int)($_POST['question_id'] ?? 0);
    if ($choice_id) {
        $stmt = $pdo->prepare("DELETE FROM survey_choices WHERE choice_id = ?");
        $stmt->execute([$choice_id]);
        set_flash('success', 'Choice removed.');
    }
    redirect('question_edit.php?id=' . $question_id);
}
redirect('survey_list.php');
