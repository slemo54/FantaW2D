<?php
require_once '../includes/config.php';
require_once '../includes/config_db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $display_name = $_POST['display_name'] ?? $username;
    $email = $_POST['email'] ?? '';
    
    // Validate
    if (empty($username) || empty($password)) {
        set_notification('Username and password are required', 'error');
        header('Location: ../admin.php');
        exit;
    }
    
    // Check if username exists
    if (get_user_by_username($username)) {
        set_notification('Username already exists', 'error');
        header('Location: ../admin.php');
        exit;
    }
    
    // Add new user
    $user_data = [
        'username' => $username,
        'password' => $password,
        'role' => $role,
        'display_name' => $display_name,
        'email' => $email
    ];
    
    if (add_user($user_data)) {
        set_notification('User added successfully');
    } else {
        set_notification('Failed to add user', 'error');
    }
}

header('Location: ../admin.php');
exit;