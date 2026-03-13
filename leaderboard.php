<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require login
require_login();

$leaderboard = get_leaderboard();
$current_user = get_logged_in_user();

include 'includes/header.php';
?>

<div class="card">
    <h2>Leaderboard</h2>
    <p>Users with the lowest balance are at the top!</p>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Name</th>
                    <th>Balance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaderboard as $rank => $user): ?>
                    <tr <?php echo $user['id'] == $current_user['id'] ? 'style="font-weight: bold; background-color: #e3f2fd;"' : ''; ?>>
                        <td><?php echo $rank + 1; ?></td>
                        <td><?php echo isset($user['display_name']) ? $user['display_name'] : $user['username']; ?></td>
                        <td class="<?php echo $user['balance'] < 0 ? 'negative' : ''; ?>">
                            <?php echo format_currency($user['balance']); ?>
                        </td>
                        <td>
                            <?php if ($user['jolly_used']): ?>
                                <span class="badge badge-used"><i class="fas fa-times-circle"></i> Jolly Used</span>
                            <?php else: ?>
                                <span class="badge badge-available"><i class="fas fa-check-circle"></i> Jolly Available</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Mobile-Friendly Cards View -->
    <div class="leaderboard-cards">
        <?php foreach ($leaderboard as $rank => $user): ?>
            <div class="leaderboard-card <?php echo $user['id'] == $current_user['id'] ? 'current-user' : ''; ?>">
                <div class="rank">#<?php echo $rank + 1; ?></div>
                <div class="user-details">
                    <div class="user-name"><?php echo isset($user['display_name']) ? $user['display_name'] : $user['username']; ?></div>
                    <div class="user-balance <?php echo $user['balance'] < 0 ? 'negative' : ''; ?>">
                        <?php echo format_currency($user['balance']); ?>
                    </div>
                    <div class="user-jolly">
                        <?php if ($user['jolly_used']): ?>
                            <span class="badge badge-used"><i class="fas fa-times-circle"></i> Jolly Used</span>
                        <?php else: ?>
                            <span class="badge badge-available"><i class="fas fa-check-circle"></i> Available</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>