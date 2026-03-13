<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin
require_admin();

// Reset all jolly
if (isset($_GET['reset_all']) && $_GET['reset_all'] == 1) {
    if (reset_all_jolly()) {
        set_notification('All Jolly have been reset');
    } else {
        set_notification('Failed to reset all Jolly', 'error');
    }
    
    header('Location: ../admin.php');
    exit;
}

// Reset single user's jolly
if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    
    if (reset_jolly($user_id)) {
        set_notification('Jolly reset successfully');
    } else {
        set_notification('Failed to reset Jolly', 'error');
    }
}

header('Location: ../admin.php');
exit;