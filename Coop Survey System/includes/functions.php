<?php
/**
 * Shared helper functions.
 */

function clean($value) {
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function truncate_text($text, $length = 100, $suffix = '...') {
    $text = $text ?? '';
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function render_flash() {
    $flash = get_flash();
    if ($flash) {
        $type = $flash['type'] === 'error' ? 'danger' : $flash['type'];
        echo '<div class="alert alert-' . clean($type) . ' alert-dismissible fade show" role="alert">'
           . clean($flash['message'])
           . '<button type="button" class="btn-close" onclick="this.closest(\'.alert\').remove()" aria-label="Close">&times;</button></div>';
    }
}

function is_member_logged_in() {
    return isset($_SESSION['member_id']);
}

function is_staff_logged_in() {
    return isset($_SESSION['staff_id']);
}

function require_member_login() {
    if (!is_member_logged_in()) {
        redirect('login.php');
    }
}

function require_staff_login() {
    if (!is_staff_logged_in()) {
        redirect('login.php');
    }
}

function log_login($pdo, $user_type, $user_id) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = $pdo->prepare("INSERT INTO login_history (user_type, user_id, ip_address) VALUES (?, ?, ?)");
    $stmt->execute([$user_type, $user_id, $ip]);
}

/**
 * Returns 'active' if today's date/time is within the survey's open/close window
 * and its status column is 'active'; otherwise returns 'draft' or 'closed'.
 */
function get_effective_survey_status($survey) {
    $now = new DateTime();
    $open = new DateTime($survey['open_date']);
    $close = new DateTime($survey['close_date']);

    if ($survey['status'] === 'draft') return 'draft';
    if ($survey['status'] === 'closed') return 'closed';
    if ($now < $open) return 'upcoming';
    if ($now > $close) return 'closed';
    return 'active';
}

function status_badge($status) {
    $map = [
        'active'   => 'success',
        'upcoming' => 'info',
        'closed'   => 'secondary',
        'draft'    => 'warning',
    ];
    $color = $map[$status] ?? 'secondary';
    return '<span class="badge text-bg-' . $color . '">' . ucfirst(clean($status)) . '</span>';
}

function question_type_label($type) {
    $labels = [
        'multiple_choice' => 'Multiple Choice',
        'yes_no'          => 'Yes / No',
        'rating'          => 'Rating Scale (1-5)',
        'short_answer'    => 'Short Answer',
    ];
    return $labels[$type] ?? $type;
}
