<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require login
require_login();

// Get current user
$current_user = get_logged_in_user();

// Check if user can assign transactions
if (!can_assign_transactions($current_user)) {
    error_log("User " . $current_user['username'] . " tried to assign a transaction but doesn't have permission");
    set_notification('You do not have permission to assign transactions', 'error');
    header('Location: ../dashboard.php');
    exit;
}

// Log debug info about current user and permissions
error_log("Transaction request from: " . $current_user['username'] . ", Role: " . $current_user['role']);
error_log("Can assign transactions: " . (can_assign_transactions($current_user) ? "YES" : "NO"));

// Support both POST and GET (for direct links)
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['direct'])) {
    // Handle data from either POST or GET
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $type = $_POST['type'] ?? '';
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        
        // Handle custom descriptions
        if (isset($_POST['custom_description']) && !empty($_POST['custom_description']) && ($_POST['description'] === 'custom')) {
            $description = $_POST['custom_description'];
        } else {
            $description = $_POST['description'] ?? '';
        }
    } else {
        // Direct link mode (GET)
        $type = $_GET['type'] ?? '';
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        
        // For direct links, create a simple description
        if ($type === 'malus') {
            $description = "Quick malus from mobile";
        } else {
            $description = "Quick bonus from mobile";
        }
    }
    
    $current_user = get_logged_in_user();
    
    // Debug info
    error_log("Transaction request from user ID: " . $current_user['id'] . " (" . ($current_user['username'] ?? 'unknown') . ")");
    error_log("Target user ID: " . $user_id);
    
    // Validate user exists
    $user = get_user_by_id($user_id);
    if (!$user) {
        error_log("ERROR: Target user not found with ID: " . $user_id);
        set_notification('User not found', 'error');
        header('Location: ../dashboard.php');
        exit;
    }
    
    error_log("Target user found: " . ($user['username'] ?? 'unknown') . " with ID: " . $user['id']);
    
    if ($type === 'malus') {
        $malus_type_id = isset($_POST['malus_type']) ? intval($_POST['malus_type']) : 1; // Default to type 1 for direct links
        
        // Validate malus type
        if (!isset($malus_types[$malus_type_id])) {
            set_notification('Invalid malus type', 'error');
            header('Location: ../dashboard.php');
            exit;
        }
        
        $malus = $malus_types[$malus_type_id];
        
        // Create transaction
        $transaction = [
            'type' => 'malus',
            'sub_type' => $malus['name'],
            'user_id' => $user_id,
            'created_by' => $current_user['id'],
            'amount' => $malus['amount'],
            'description' => $description
        ];
        
        add_transaction($transaction);
        set_notification('Malus added successfully');
        
    } elseif ($type === 'bonus') {
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.50; // Default to 0.50 for direct links
        
        // Validate amount
        if (!in_array($amount, $bonus_amounts)) {
            set_notification('Invalid bonus amount', 'error');
            header('Location: ../dashboard.php');
            exit;
        }
        
        // Create transaction
        $transaction = [
            'type' => 'bonus',
            'user_id' => $user_id,
            'created_by' => $current_user['id'],
            'amount' => $amount,
            'description' => $description
        ];
        
        add_transaction($transaction);
        set_notification('Bonus added successfully');
        
    } else {
        set_notification('Invalid transaction type', 'error');
    }
}

header('Location: ../dashboard.php');
exit;