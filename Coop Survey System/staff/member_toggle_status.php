<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = (int)($_POST['member_id'] ?? 0);
    if ($member_id) {
        $stmt = $pdo->prepare("SELECT status FROM members WHERE member_id = ?");
        $stmt->execute([$member_id]);
        $row = $stmt->fetch();
        if ($row) {
            $new_status = $row['status'] === 'active' ? 'inactive' : 'active';
            $stmt = $pdo->prepare("UPDATE members SET status = ? WHERE member_id = ?");
            $stmt->execute([$new_status, $member_id]);
            set_flash('success', 'Member status updated.');
        }
    }
}

redirect('member_list.php');
