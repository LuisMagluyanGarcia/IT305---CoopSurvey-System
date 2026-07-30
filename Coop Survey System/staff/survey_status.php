<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $survey_id = (int)($_POST['survey_id'] ?? 0);
    $new_status = in_array($_POST['new_status'] ?? '', ['draft', 'active', 'closed']) ? $_POST['new_status'] : null;

    if ($survey_id && $new_status) {
        $stmt = $pdo->prepare("UPDATE surveys SET status = ? WHERE survey_id = ?");
        $stmt->execute([$new_status, $survey_id]);
        set_flash('success', 'Survey status updated.');
    } else {
        set_flash('error', 'Invalid request.');
    }
}

redirect('survey_list.php');
