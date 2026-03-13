<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require login
require_login();

$current_user = get_logged_in_user();

// Process email update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_email'])) {
    $email = $_POST['email'] ?? '';
    
    // Update user email
    $current_user['email'] = $email;
    
    if (update_user($current_user)) {
        set_notification('Email updated successfully');
    } else {
        set_notification('Failed to update email', 'error');
    }
}

// Process password change (not implemented in demo)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    set_notification('Password change is not implemented in this demo version', 'warning');
}

include 'includes/header.php';
?>

<div class="card">
    <h2>User Profile</h2>
    
    <div style="margin-bottom: 20px;">
        <h3><?php echo isset($current_user['display_name']) ? $current_user['display_name'] : $current_user['username']; ?></h3>
        <p>Username: <?php echo $current_user['username']; ?></p>
        <p>Role: <?php echo ucfirst($current_user['role']); ?></p>
        <p>Balance: <span class="<?php echo $current_user['balance'] < 0 ? 'negative' : ''; ?>">
            <?php echo format_currency($current_user['balance']); ?>
        </span></p>
        <p>Jolly Status: 
            <?php if ($current_user['jolly_used']): ?>
                <span style="color: var(--secondary-color);">Used</span>
            <?php else: ?>
                <span style="color: var(--primary-color);">Available</span>
            <?php endif; ?>
        </p>
    </div>
    
    <?php 
    // Get user-specific malus rules
    $user_name = isset($current_user['display_name']) ? $current_user['display_name'] : $current_user['username'];
    $malus_rules = isset($user_specific_malus[$user_name]) ? $user_specific_malus[$user_name] : null;
    
    if ($malus_rules): 
    ?>
    <div style="margin-bottom: 20px; background-color: #f8f9fa; padding: 15px; border-radius: 5px;">
        <h3>Your Malus Rules</h3>
        <ul>
            <?php if (!empty($malus_rules['malus1'])): ?>
                <li><strong>Malus #1 (€0.50):</strong> <?php echo $malus_rules['malus1']; ?></li>
            <?php endif; ?>
            
            <?php if (!empty($malus_rules['malus2'])): ?>
                <li><strong>Malus #2 (€1.00):</strong> <?php echo $malus_rules['malus2']; ?></li>
            <?php endif; ?>
            
            <?php if (!empty($malus_rules['extra'])): ?>
                <li><strong>Extra Malus (€2.00):</strong> <?php echo $malus_rules['extra']; ?></li>
            <?php endif; ?>
        </ul>
        <?php if (empty($malus_rules['malus1']) && empty($malus_rules['malus2']) && empty($malus_rules['extra'])): ?>
            <p>You don't have any predefined malus rules.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="form-group" style="margin-bottom: 30px;">
        <h3>Update Email Address</h3>
        <p>Provide your email address to receive notifications about malus, bonus, and jolly usage.</p>
        <form method="post" action="profile.php">
            <input type="hidden" name="update_email" value="1">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($current_user['email']) ? $current_user['email'] : ''; ?>">
            </div>
            <button type="submit" class="btn btn-primary">Update Email</button>
        </form>
    </div>
    
    <div class="form-group">
        <h3>Change Password</h3>
        <form method="post" action="profile.php">
            <input type="hidden" name="change_password" value="1">
            <div class="form-group">
                <label for="current-password">Current Password</label>
                <input type="password" id="current-password" name="current_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="new-password">New Password</label>
                <input type="password" id="new-password" name="new_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirm-password">Confirm New Password</label>
                <input type="password" id="confirm-password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
        <p><em>Note: Password change is not implemented in this demo version.</em></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>