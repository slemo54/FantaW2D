<?php 
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

initialize_demo_data();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#3498db">
    <title><?php echo $app_name; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <!-- Favicon and PWA Icons -->
    <link rel="icon" type="image/png" href="assets/img/icon-192x192.png">
    <link rel="apple-touch-icon" href="assets/img/icon-192x192.png">
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <!-- Notification area -->
    <?php $notification = get_notification(); ?>
    <?php if ($notification): ?>
    <div class="notification <?php echo $notification['type']; ?>">
        <?php echo $notification['message']; ?>
    </div>
    <?php endif; ?>
    
    <div class="container">
        <?php if (is_logged_in()): ?>
        <header>
            <h1><?php echo $app_name; ?></h1>
            <div class="user-info">
                <p>Welcome, <?php echo get_logged_in_user()['username']; ?>!</p>
                <a href="profile.php" class="btn btn-secondary">Profile</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
            
            <nav>
                <ul class="nav-tabs">
                    <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="leaderboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'leaderboard.php' ? 'active' : ''; ?>">Leaderboard</a></li>
                    <li><a href="transactions.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>">Transactions</a></li>
                    <?php if (is_admin(get_logged_in_user())): ?>
                    <li><a href="admin.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">Admin</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>
        <?php else: ?>
        <header>
            <h1><?php echo $app_name; ?></h1>
        </header>
        <?php endif; ?>