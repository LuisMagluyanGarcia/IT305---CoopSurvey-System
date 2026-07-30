<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_staff_login();

if (($_SESSION['staff_role'] ?? '') !== 'admin') {
    set_flash('error', 'Only administrators can perform this action.');
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = (int)($_POST['staff_id'] ?? 0);

    if ($staff_id === (int)$_SESSION['staff_id']) {
        set_flash('error', 'You cannot deactivate your own account.');
        redirect('user_management.php');
    }

    if ($staff_id) {
        $stmt = $pdo->prepare("SELECT status FROM staff WHERE staff_id = ?");
        $stmt->execute([$staff_id]);
        $row = $stmt->fetch();
        if ($row) {
            $new_status = $row['status'] === 'active' ? 'inactive' : 'active';
            $stmt = $pdo->prepare("UPDATE staff SET status = ? WHERE staff_id = ?");
            $stmt->execute([$new_status, $staff_id]);
            set_flash('success', 'Staff account status updated.');
        }
    }
}

redirect('user_management.php');
