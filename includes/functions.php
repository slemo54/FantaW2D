<?php
require_once 'config.php';
require_once 'config_db.php';
require_once 'email_notifications.php';

// Inizializza la classe per le notifiche email
$emailNotifications = new EmailNotifications();

/**
 * Format amount as currency
 */
function format_currency($amount) {
    return '€' . number_format($amount, 2, '.', ',');
}

/**
 * Get user by ID
 */
function get_user_by_id($user_id) {
    global $demo_users;
    
    // Controllo per modalità setup
    if (isset($_SESSION['setup_mode']) && $_SESSION['setup_mode'] && $user_id == 1) {
        // Restituisci un utente admin in modalità setup
        return [
            'id' => 1,
            'username' => 'admin',
            'password' => 'admin123',
            'role' => 'admin',
            'display_name' => 'Administrator (Setup Mode)',
            'balance' => 0.00,
            'jolly_used' => false
        ];
    }
    
    if (is_using_database()) {
        try {
            $sql = "SELECT * FROM users WHERE id = ?";
            $user = db_get_row($sql, [$user_id]);
            
            if ($user) {
                return $user;
            }
        } catch (Exception $e) {
            error_log("Errore nel recupero utente: " . $e->getMessage());
            
            // Se siamo in modalità setup e l'utente è admin
            if ($user_id == 1) {
                // Restituisci un utente admin in modalità setup
                $_SESSION['setup_mode'] = true;
                return [
                    'id' => 1,
                    'username' => 'admin',
                    'password' => 'admin123',
                    'role' => 'admin',
                    'display_name' => 'Administrator (Setup Mode)',
                    'balance' => 0.00,
                    'jolly_used' => false
                ];
            }
        }
    }
    
    // Fallback alla memorizzazione in sessione
    foreach ($demo_users as $user) {
        if ($user['id'] == $user_id) {
            return $user;
        }
    }
    
    return null;
}

/**
 * Get user by username
 */
function get_user_by_username($username) {
    global $demo_users;
    
    if (is_using_database()) {
        $sql = "SELECT * FROM users WHERE username = ?";
        return db_get_row($sql, [$username]);
    }
    
    // Fallback alla memorizzazione in sessione
    foreach ($demo_users as $user) {
        if ($user['username'] == $username) {
            return $user;
        }
    }
    
    return null;
}

/**
 * Get user-specific malus rules
 */
function get_user_malus_rules($user_id) {
    global $user_specific_malus;
    
    if (is_using_database()) {
        $sql = "SELECT * FROM user_malus_rules WHERE user_id = ?";
        $rules = db_query($sql, [$user_id]);
        
        $result = [
            'malus1' => '',
            'malus2' => '',
            'extra' => ''
        ];
        
        foreach ($rules as $rule) {
            $result[$rule['malus_type']] = $rule['description'];
        }
        
        return $result;
    }
    
    // Fallback alla memorizzazione in config
    $user = get_user_by_id($user_id);
    if ($user && isset($user['display_name']) && isset($user_specific_malus[$user['display_name']])) {
        return $user_specific_malus[$user['display_name']];
    }
    
    return [
        'malus1' => '',
        'malus2' => '',
        'extra' => ''
    ];
}

/**
 * Get bonus rules
 */
function get_bonus_rules() {
    global $positive_actions;
    
    if (is_using_database()) {
        $sql = "SELECT * FROM bonus_rules";
        $rules = db_query($sql);
        
        $result = [];
        foreach ($rules as $rule) {
            $result[] = $rule['description'];
        }
        
        return $result;
    }
    
    // Fallback alla memorizzazione in config
    return $positive_actions;
}

/**
 * Update user data
 */
function update_user($updated_user) {
    global $demo_users;
    
    if (is_using_database()) {
        $sql = "UPDATE users SET 
                username = ?, 
                display_name = ?, 
                role = ?, 
                balance = ?, 
                jolly_used = ? 
                WHERE id = ?";
        
        $params = [
            $updated_user['username'],
            $updated_user['display_name'],
            $updated_user['role'],
            $updated_user['balance'],
            $updated_user['jolly_used'] ? 1 : 0,
            $updated_user['id']
        ];
        
        return db_query($sql, $params) !== false;
    }
    
    // Fallback alla memorizzazione in sessione
    foreach ($demo_users as $key => $user) {
        if ($user['id'] == $updated_user['id']) {
            $demo_users[$key] = $updated_user;
            $_SESSION['users'] = $demo_users; // Store updated users in session
            return true;
        }
    }
    
    return false;
}

/**
 * Get all transactions
 */
function get_all_transactions() {
    if (is_using_database()) {
        $sql = "SELECT * FROM transactions ORDER BY timestamp DESC";
        return db_query($sql);
    }
    
    // Fallback alla memorizzazione in sessione
    if (isset($_SESSION['transactions'])) {
        return $_SESSION['transactions'];
    }
    
    return [];
}

/**
 * Get transactions for a specific user
 * This function now returns ALL transactions for all users
 */
function get_user_transactions($user_id) {
    // For now, just return all transactions so all users can see everything
    return get_all_transactions();
    
    // Original implementation below - commented out to allow all users to see all transactions
    /*
    if (is_using_database()) {
        $sql = "SELECT * FROM transactions 
                WHERE user_id = ? OR created_by = ? 
                ORDER BY timestamp DESC";
        
        return db_query($sql, [$user_id, $user_id]);
    }
    
    // Fallback alla memorizzazione in sessione
    $transactions = get_all_transactions();
    $user_transactions = [];
    
    foreach ($transactions as $transaction) {
        if ($transaction['user_id'] == $user_id || $transaction['created_by'] == $user_id) {
            $user_transactions[] = $transaction;
        }
    }
    
    // Sort by timestamp, newest first
    usort($user_transactions, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
    
    return $user_transactions;
    */
}

/**
 * Add a new transaction
 */
function add_transaction($transaction) {
    global $emailNotifications;
    
    // Debug transaction
    error_log("Adding transaction: " . json_encode($transaction));
    error_log("Created by user ID: " . $transaction['created_by']);
    error_log("Target user ID: " . $transaction['user_id']);
    
    if (is_using_database()) {
        $sql = "INSERT INTO transactions (
                    type, user_id, created_by, amount, description, sub_type, cancelled
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $transaction['type'],
            $transaction['user_id'],
            $transaction['created_by'],
            $transaction['amount'],
            $transaction['description'],
            isset($transaction['sub_type']) ? $transaction['sub_type'] : null,
            isset($transaction['cancelled']) ? 1 : 0
        ];
        
        $transaction_id = db_query($sql, $params);
        
        if ($transaction_id) {
            // Update user balance
            $user = get_user_by_id($transaction['user_id']);
            
            if ($user) {
                if ($transaction['type'] == 'malus' && (!isset($transaction['cancelled']) || !$transaction['cancelled'])) {
                    $user['balance'] -= $transaction['amount'];
                } else if ($transaction['type'] == 'bonus') {
                    $user['balance'] += $transaction['amount'];
                }
                
                update_user($user);
                
                // Send email notification
                $creator = get_user_by_id($transaction['created_by']);
                
                // Get the transaction with ID
                $transaction['id'] = $transaction_id;
                
                // Invia email di notifica
                if ($transaction['type'] == 'malus') {
                    $emailNotifications->sendMalusNotification($user, $transaction, $creator);
                } else if ($transaction['type'] == 'bonus') {
                    $emailNotifications->sendBonusNotification($user, $transaction, $creator);
                }
            }
            
            return $transaction_id;
        }
        
        return false;
    }
    
    // Fallback alla memorizzazione in sessione
    $transactions = get_all_transactions();
    
    // Generate a new ID
    $transaction['id'] = count($transactions) + 1;
    $transaction['timestamp'] = time();
    
    $transactions[] = $transaction;
    $_SESSION['transactions'] = $transactions;
    
    // Update user balance
    $user = get_user_by_id($transaction['user_id']);
    
    if ($user) {
        if ($transaction['type'] == 'malus' && (!isset($transaction['cancelled']) || !$transaction['cancelled'])) {
            $user['balance'] -= $transaction['amount'];
        } else if ($transaction['type'] == 'bonus') {
            $user['balance'] += $transaction['amount'];
        }
        
        update_user($user);
    }
    
    return $transaction['id'];
}

/**
 * Get transaction by ID
 */
function get_transaction_by_id($transaction_id) {
    if (is_using_database()) {
        $sql = "SELECT * FROM transactions WHERE id = ?";
        return db_get_row($sql, [$transaction_id]);
    }
    
    // Fallback alla memorizzazione in sessione
    $transactions = get_all_transactions();
    
    foreach ($transactions as $transaction) {
        if ($transaction['id'] == $transaction_id) {
            return $transaction;
        }
    }
    
    return null;
}

/**
 * Update transaction
 */
function update_transaction($updated_transaction) {
    if (is_using_database()) {
        $sql = "UPDATE transactions SET 
                type = ?, 
                user_id = ?, 
                created_by = ?, 
                amount = ?, 
                description = ?, 
                sub_type = ?, 
                cancelled = ? 
                WHERE id = ?";
        
        $params = [
            $updated_transaction['type'],
            $updated_transaction['user_id'],
            $updated_transaction['created_by'],
            $updated_transaction['amount'],
            $updated_transaction['description'],
            isset($updated_transaction['sub_type']) ? $updated_transaction['sub_type'] : null,
            isset($updated_transaction['cancelled']) && $updated_transaction['cancelled'] ? 1 : 0,
            $updated_transaction['id']
        ];
        
        return db_query($sql, $params) !== false;
    }
    
    // Fallback alla memorizzazione in sessione
    $transactions = get_all_transactions();
    
    foreach ($transactions as $key => $transaction) {
        if ($transaction['id'] == $updated_transaction['id']) {
            $transactions[$key] = $updated_transaction;
            $_SESSION['transactions'] = $transactions;
            return true;
        }
    }
    
    return false;
}

/**
 * Use jolly on a malus transaction
 */
function use_jolly($user_id, $transaction_id) {
    global $emailNotifications;
    
    // Get the user
    $user = get_user_by_id($user_id);
    
    if (!$user || $user['jolly_used']) {
        return false;
    }
    
    // Get the transaction
    $transaction = get_transaction_by_id($transaction_id);
    
    if (!$transaction || $transaction['type'] != 'malus' || 
        $transaction['user_id'] != $user_id || 
        (isset($transaction['cancelled']) && $transaction['cancelled'])) {
        return false;
    }
    
    // Cancel the transaction
    $transaction['cancelled'] = true;
    update_transaction($transaction);
    
    // Mark jolly as used
    $user['jolly_used'] = true;
    
    // Refund the amount
    $user['balance'] += $transaction['amount'];
    
    update_user($user);
    
    // Invia email di notifica
    $emailNotifications->sendJollyNotification($user, $transaction);
    
    return true;
}

/**
 * Reset jolly for a user
 */
function reset_jolly($user_id) {
    global $emailNotifications;
    
    $user = get_user_by_id($user_id);
    
    if (!$user) {
        return false;
    }
    
    $user['jolly_used'] = false;
    
    if (update_user($user)) {
        // Invia email di notifica
        $emailNotifications->sendJollyResetNotification($user);
        return true;
    }
    
    return false;
}

/**
 * Reset jolly for all users
 */
function reset_all_jolly() {
    global $demo_users, $emailNotifications;
    
    if (is_using_database()) {
        $sql = "UPDATE users SET jolly_used = 0";
        
        if (db_query($sql) !== false) {
            // Invia email di notifica a tutti gli utenti
            $sql = "SELECT * FROM users WHERE role = 'user'";
            $users = db_query($sql);
            
            foreach ($users as $user) {
                $emailNotifications->sendJollyResetNotification($user);
            }
            
            return true;
        }
        
        return false;
    }
    
    // Fallback alla memorizzazione in sessione
    foreach ($demo_users as $key => $user) {
        $demo_users[$key]['jolly_used'] = false;
    }
    
    $_SESSION['users'] = $demo_users;
    
    return true;
}

/**
 * Get leaderboard (users sorted by balance)
 */
function get_leaderboard() {
    global $demo_users;
    
    if (is_using_database()) {
        $sql = "SELECT * FROM users ORDER BY balance ASC";
        return db_query($sql);
    }
    
    // Fallback alla memorizzazione in sessione
    $users = $demo_users;
    
    // Sort by balance (ascending, as lower is better in this game)
    usort($users, function($a, $b) {
        return $a['balance'] - $b['balance'];
    });
    
    return $users;
}

/**
 * Display a notification message
 */
function set_notification($message, $type = 'success') {
    $_SESSION['notification'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get and clear notification
 */
function get_notification() {
    if (isset($_SESSION['notification'])) {
        $notification = $_SESSION['notification'];
        unset($_SESSION['notification']);
        return $notification;
    }
    
    return null;
}

/**
 * Check if user is admin
 */
function is_admin($user) {
    return isset($user['role']) && $user['role'] == 'admin';
}

/**
 * Check if user can assign transactions (malus/bonus)
 * This determines whether a user can assign malus or bonus to others
 */
function can_assign_transactions($user) {
    global $special_users;
    
    // Administrators can assign transactions
    if (is_admin($user)) {
        return true;
    }
    
    // Special users can also assign transactions
    if (isset($user['username']) && isset($special_users[$user['username']])) {
        return true;
    }
    
    // NOW ALLOWING ALL USERS to assign transactions
    return true;
    
    // To restrict transactions to only admins and special users, uncomment this line:
    // return false;
}

/**
 * Add new user
 */
function add_user($user_data) {
    global $demo_users;
    
    if (is_using_database()) {
        // Hash the password
        $hashed_password = password_hash($user_data['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (
                    username, password, display_name, role, balance, jolly_used, email
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $user_data['username'],
            $hashed_password,
            $user_data['display_name'],
            $user_data['role'],
            0.00,
            0,
            isset($user_data['email']) ? $user_data['email'] : null
        ];
        
        return db_query($sql, $params);
    }
    
    // Fallback alla memorizzazione in sessione
    $new_user = [
        'id' => count($demo_users) + 1,
        'username' => $user_data['username'],
        'password' => $user_data['password'], // In production, use password_hash()
        'role' => $user_data['role'],
        'balance' => 0.00,
        'jolly_used' => false,
        'display_name' => $user_data['display_name']
    ];
    
    $demo_users[] = $new_user;
    $_SESSION['users'] = $demo_users;
    
    return $new_user['id'];
}

/**
 * Initialize demo data if not already in session
 */
function initialize_demo_data() {
    global $demo_users;
    
    if (is_using_database()) {
        // No need to initialize session data when using database
        error_log("Usando database, non inizializzo dati demo");
        return;
    }
    
    error_log("Inizializzazione dati demo, controllo sessione users");
    
    // Assicuriamoci che l'array degli utenti demo sia sempre disponibile
    if (!isset($_SESSION['users'])) {
        error_log("La sessione users non esiste, creo da demo_users con " . count($demo_users) . " utenti");
        $_SESSION['users'] = $demo_users;
        
        // Debug utenti creati
        foreach ($_SESSION['users'] as $user) {
            error_log("Utente salvato in sessione: " . $user['username'] . " / " . $user['password']);
        }
    } else {
        error_log("La sessione users esiste già con " . count($_SESSION['users']) . " utenti");
        // Sincronizza con gli utenti demo
        $demo_users = $_SESSION['users'];
    }
    
    if (!isset($_SESSION['transactions'])) {
        error_log("Inizializzazione transazioni vuote");
        $_SESSION['transactions'] = [];
    }
}