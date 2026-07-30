<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_member_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('available_surveys.php');
}

$member_id = $_SESSION['member_id'];
$survey_id = (int)($_POST['survey_id'] ?? 0);
$answers = $_POST['answers'] ?? [];

$stmt = $pdo->prepare("SELECT * FROM surveys WHERE survey_id = ?");
$stmt->execute([$survey_id]);
$survey = $stmt->fetch();

if (!$survey || get_effective_survey_status($survey) !== 'active') {
    set_flash('error', 'This survey is no longer open for responses.');
    redirect('available_surveys.php');
}

// Prevent duplicate submissions
$stmt = $pdo->prepare("SELECT response_id FROM responses WHERE survey_id = ? AND member_id = ?");
$stmt->execute([$survey_id, $member_id]);
if ($stmt->fetch()) {
    set_flash('info', 'You have already answered this survey.');
    redirect('available_surveys.php');
}

$stmt = $pdo->prepare("SELECT * FROM survey_questions WHERE survey_id = ?");
$stmt->execute([$survey_id]);
$questions = $stmt->fetchAll();

// Server-side validation of required questions
foreach ($questions as $q) {
    if (!$q['is_required']) continue;
    $qid = $q['question_id'];
    $a = $answers[$qid] ?? null;

    $ok = false;
    if ($q['question_type'] === 'multiple_choice') {
        $ok = !empty($a['choice_id']);
    } elseif ($q['question_type'] === 'yes_no') {
        $ok = !empty($a['text']);
    } elseif ($q['question_type'] === 'rating') {
        $ok = isset($a['rating']) && $a['rating'] !== '';
    } elseif ($q['question_type'] === 'short_answer') {
        $ok = !empty(trim($a['text'] ?? ''));
    }

    if (!$ok) {
        set_flash('error', 'Please answer all required questions.');
        redirect('survey_form.php?id=' . $survey_id);
    }
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO responses (survey_id, member_id) VALUES (?, ?)");
    $stmt->execute([$survey_id, $member_id]);
    $response_id = $pdo->lastInsertId();

    $insertAnswer = $pdo->prepare("
        INSERT INTO response_answers (response_id, question_id, choice_id, rating_value, answer_text)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($questions as $q) {
        $qid = $q['question_id'];
        $a = $answers[$qid] ?? null;
        if ($a === null) continue;

        $choice_id = null;
        $rating = null;
        $text = null;

        if ($q['question_type'] === 'multiple_choice' && !empty($a['choice_id'])) {
            $choice_id = (int)$a['choice_id'];
        } elseif ($q['question_type'] === 'yes_no' && !empty($a['text'])) {
            $text = $a['text'] === 'Yes' ? 'Yes' : 'No';
        } elseif ($q['question_type'] === 'rating' && $a['rating'] !== '') {
            $rating = max(1, min(5, (int)$a['rating']));
        } elseif ($q['question_type'] === 'short_answer' && !empty(trim($a['text'] ?? ''))) {
            $text = trim($a['text']);
        } else {
            continue; // optional question left blank
        }

        $insertAnswer->execute([$response_id, $qid, $choice_id, $rating, $text]);
    }

    $pdo->commit();
    redirect('confirmation.php?survey_id=' . $survey_id);

} catch (Exception $e) {
    $pdo->rollBack();
    set_flash('error', 'Something went wrong while submitting your response. Please try again.');
    redirect('survey_form.php?id=' . $survey_id);
}
