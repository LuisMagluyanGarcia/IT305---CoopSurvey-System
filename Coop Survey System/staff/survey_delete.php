<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $survey_id = (int)($_POST['survey_id'] ?? 0);
    if ($survey_id) {
        $stmt = $pdo->prepare("DELETE FROM surveys WHERE survey_id = ?");
        $stmt->execute([$survey_id]);
        set_flash('success', 'Survey deleted.');
    }
}

redirect('survey_list.php');
