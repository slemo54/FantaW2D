<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require admin
require_admin();

// Get all users and all transactions
$users = $demo_users;
$all_transactions = get_all_transactions();

include 'includes/header.php';
?>

<div class="card">
    <h2>User Management</h2>
    
    <div class="user-list">
        <?php foreach ($users as $user): ?>
            <div class="user-item">
                <div>
                    <strong><?php echo isset($user['display_name']) ? $user['display_name'] : $user['username']; ?></strong> (<?php echo $user['role']; ?>)
                    <br>
                    <small>
                        Balance: <span class="<?php echo $user['balance'] < 0 ? 'negative' : ''; ?>">
                            <?php echo format_currency($user['balance']); ?>
                        </span>
                        <?php if (!empty($user['email'])): ?>
                            | Email: <?php echo $user['email']; ?>
                        <?php endif; ?>
                    </small>
                </div>
                <div>
                    <button class="btn btn-accent" 
                            <?php echo $user['jolly_used'] ? '' : 'disabled'; ?>
                            onclick="location.href='api/reset_jolly.php?user_id=<?php echo $user['id']; ?>'">
                        <?php echo $user['jolly_used'] ? 'Reset Jolly' : 'Jolly Not Used'; ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <button class="btn btn-primary" data-modal="add-user-modal">Add New User</button>
</div>

<div class="card">
    <h2>Reset All Jolly</h2>
    <p>Reset the Jolly for all users at once.</p>
    <button class="btn btn-accent" 
            data-confirm="Are you sure you want to reset all Jolly?"
            onclick="location.href='api/reset_jolly.php?reset_all=1'">
        Reset All Jolly
    </button>
</div>

<div class="card">
    <h2>Database Management</h2>
    <p>Run these actions to set up or reset your database:</p>
    <div style="margin-top: 10px;">
        <a href="db_setup.php" class="btn btn-primary" data-confirm="This will create or update the database tables. Are you sure?">Setup Database</a>
        <a href="db_reset.php" class="btn btn-danger" data-confirm="This will RESET all data in the database! Are you sure?">Reset Database</a>
    </div>
</div>

<div class="card">
    <h2>All Transactions</h2>
    
    <?php if (empty($all_transactions)): ?>
        <p>No transactions yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>User</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Created By</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_transactions as $transaction): ?>
                    <?php 
                    $user = get_user_by_id($transaction['user_id']);
                    $creator = get_user_by_id($transaction['created_by']);
                    $is_cancelled = isset($transaction['cancelled']) && $transaction['cancelled'];
                    ?>
                    <tr class="<?php echo $is_cancelled ? 'cancelled' : ''; ?>">
                        <td><?php echo $transaction['id']; ?></td>
                        <td>
                            <?php if ($transaction['type'] == 'malus'): ?>
                                <span class="malus"><?php echo $transaction['sub_type']; ?></span>
                            <?php else: ?>
                                <span class="bonus">Bonus</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo isset($user['display_name']) ? $user['display_name'] : $user['username']; ?></td>
                        <td>
                            <?php if ($transaction['type'] == 'malus'): ?>
                                <span class="malus">-<?php echo format_currency($transaction['amount']); ?></span>
                            <?php else: ?>
                                <span class="bonus">+<?php echo format_currency($transaction['amount']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $transaction['description']; ?></td>
                        <td><?php echo isset($creator['display_name']) ? $creator['display_name'] : $creator['username']; ?></td>
                        <td><?php echo is_numeric($transaction['timestamp']) ? date('d/m/Y H:i', $transaction['timestamp']) : date('d/m/Y H:i', strtotime($transaction['timestamp'])); ?></td>
                        <td>
                            <?php if ($is_cancelled): ?>
                                <span style="color: var(--secondary-color);">Cancelled</span>
                            <?php else: ?>
                                <span style="color: var(--primary-color);">Active</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Add User Modal -->
<div id="add-user-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Add New User</h2>
        <form method="post" action="api/add_user.php">
            <div class="form-group">
                <label for="display-name">Display Name</label>
                <input type="text" id="display-name" name="display_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="new-username">Username</label>
                <input type="text" id="new-username" name="username" class="form-control" required>
                <small>Username will be used for login (alphanumeric, no spaces)</small>
            </div>
            <div class="form-group">
                <label for="new-password">Password</label>
                <input type="password" id="new-password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control">
                <small>Used for notification emails</small>
            </div>
            <div class="form-group">
                <label for="user-role">Role</label>
                <select id="user-role" name="role" class="form-control">
                    <option value="user">Standard User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add User</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>