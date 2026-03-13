<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require login
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : 0;
    $current_user = get_logged_in_user();
    
    // Check if user is admin
    if (!is_admin($current_user)) {
        set_notification('Only administrators can use Jolly', 'error');
        header('Location: ../dashboard.php');
        exit;
    }
    
    // Get the transaction
    $transaction = get_transaction_by_id($transaction_id);
    if (!$transaction) {
        set_notification('Transaction not found', 'error');
        header('Location: ../dashboard.php');
        exit;
    }
    
    // For admins, we'll allow canceling any transaction (both malus and bonus)
    if (is_admin($current_user)) {
        // Mark transaction as cancelled
        $transaction['cancelled'] = true;
        
        if (update_transaction($transaction)) {
            // Adjust the user's balance based on transaction type
            $affected_user = get_user_by_id($transaction['user_id']);
            if ($affected_user) {
                if ($transaction['type'] == 'malus') {
                    // Refund malus amount (add balance back)
                    $affected_user['balance'] += $transaction['amount'];
                } else if ($transaction['type'] == 'bonus') { 
                    // Subtract bonus amount (it was wrongly given)
                    $affected_user['balance'] -= $transaction['amount'];
                }
                
                update_user($affected_user);
                
                // Note: Not marking jolly as used for admin cancellations
                $transaction_type = ucfirst($transaction['type']);
                set_notification($transaction_type . ' transaction cancelled successfully');
            } else {
                set_notification('Failed to update user balance', 'error');
            }
        } else {
            set_notification('Failed to cancel transaction', 'error');
        }
    } else {
        // Regular users would use their jolly (currently disabled)
        set_notification('Only administrators can cancel transactions', 'error');
    }
}

header('Location: ../dashboard.php');
exit;