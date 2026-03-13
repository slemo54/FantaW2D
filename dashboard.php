<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require login
require_login();

$current_user = get_logged_in_user();
$recent_transactions = get_user_transactions($current_user['id']);

// Only show the 5 most recent transactions
$recent_transactions = is_array($recent_transactions) ? array_slice($recent_transactions, 0, 5) : [];

include 'includes/header.php';
?>

<div class="card balance-card">
    <h2>Your Balance</h2>
    <div class="amount <?php echo $current_user['balance'] < 0 ? 'negative' : ''; ?>">
        <?php echo format_currency($current_user['balance']); ?>
    </div>
    <div style="margin-top: 20px;">
        <?php if (is_admin($current_user)): ?>
        <button class="btn btn-accent" <?php echo $current_user['jolly_used'] ? 'disabled' : ''; ?> data-modal="use-jolly-modal">
            <i class="fas fa-magic"></i> <?php echo $current_user['jolly_used'] ? 'Jolly Already Used' : 'Use Jolly'; ?>
        </button>
        <p><small><?php echo $current_user['jolly_used'] ? 'You\'ve already used your Jolly this month' : 'You can use your Jolly once per month to cancel a malus'; ?></small></p>
        <?php else: ?>
        <button class="btn btn-secondary" disabled>
            <i class="fas fa-magic"></i> Jolly (Admin Only)
        </button>
        <p><i class="fas fa-info-circle"></i> <small>Only administrators can cancel transactions</small></p>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <h2>Quick Actions</h2>
    
    <!-- DIRECT LINKS instead of modal buttons -->
    <div class="quick-actions">
        <a href="direct_action.php?action=malus" class="btn btn-secondary" id="add-malus-link">
            <i class="fas fa-minus-circle"></i> Report Malus
        </a>
        <a href="direct_action.php?action=bonus" class="btn btn-primary" id="add-bonus-link">
            <i class="fas fa-plus-circle"></i> Add Bonus
        </a>
    </div>
    
    <!-- Old modal buttons (hidden but kept for compatibility) -->
    <div style="display: none;">
        <button class="btn btn-secondary" data-modal="add-malus-modal" id="add-malus-btn">
            <i class="fas fa-minus-circle"></i> Report Malus
        </button>
        <button class="btn btn-primary" data-modal="add-bonus-modal" id="add-bonus-btn">
            <i class="fas fa-plus-circle"></i> Add Bonus
        </button>
    </div>
    
    <p><small><i class="fas fa-info-circle"></i> <strong>IMPORTANTE:</strong> Ora TUTTI gli utenti possono assegnare malus e bonus!</small></p>
    
    <!-- Debug info -->
    <div style="border-top: 1px dashed #ccc; margin-top: 10px; padding-top: 10px; font-size: 0.9em;">
        <p><strong>Il tuo account:</strong> <?php echo $current_user['username']; ?></p>
        <p><strong>Ruolo:</strong> <?php echo $current_user['role']; ?></p>
        <p><strong>Permessi:</strong> <?php echo can_assign_transactions($current_user) ? 'Puoi assegnare malus/bonus' : 'Non puoi assegnare malus/bonus'; ?></p>
    </div>
    
    <?php if (isset($_GET['direct']) || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone') !== false || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad') !== false): ?>
    <div style="margin-top: 20px; border-top: 1px dashed #ccc; padding-top: 10px;">
        <h3><i class="fas fa-link"></i> Quick Links</h3>
        <p><small>Alternative options if you experience issues with the buttons above:</small></p>
        
        <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
            <!-- Quick users for Malus -->
            <div style="margin-bottom: 15px;">
                <strong><i class="fas fa-minus-circle"></i> Quick Malus:</strong>
                <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;">
                    <?php foreach (array_slice($demo_users, 0, 8) as $user): ?>
                        <?php if ($user['id'] != 1): // Skip admin ?>
                            <a href="direct_action.php?action=malus&target_id=<?php echo $user['id']; ?>" class="btn btn-secondary" style="font-size: 0.9em; padding: 8px 12px; margin: 2px;">
                                <?php echo isset($user['display_name']) ? explode(' ', $user['display_name'])[0] : $user['username']; ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Quick users for Bonus -->
            <div>
                <strong><i class="fas fa-plus-circle"></i> Quick Bonus:</strong>
                <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;">
                    <?php foreach (array_slice($demo_users, 0, 8) as $user): ?>
                        <?php if ($user['id'] != 1): // Skip admin ?>
                            <a href="direct_action.php?action=bonus&target_id=<?php echo $user['id']; ?>" class="btn btn-primary" style="font-size: 0.9em; padding: 8px 12px; margin: 2px;">
                                <?php echo isset($user['display_name']) ? explode(' ', $user['display_name'])[0] : $user['username']; ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['debug']) || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone') !== false || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad') !== false): ?>
    <div style="margin-top: 20px; border-top: 1px dashed #ccc; padding-top: 10px;">
        <h3><i class="fas fa-wrench"></i> Advanced Tools</h3>
        <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
            <a href="direct_action.php" class="btn btn-accent" style="text-align: center;">
                <i class="fas fa-cogs"></i> Direct Actions Menu
            </a>
            <a href="simple_action.php" class="btn btn-accent" style="text-align: center;">
                <i class="fas fa-tools"></i> Super Simple Actions
            </a>
            <button onclick="submitDebugInfo()" class="btn btn-accent" style="width: 100%;">
                <i class="fas fa-bug"></i> Debug Info
            </button>
        </div>
        <p><small>Utilizzare questi strumenti se hai problemi ad assegnare malus o bonus</small></p>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Recent Transactions</h2>
    <?php if (empty($recent_transactions)): ?>
        <p>No transactions yet.</p>
    <?php else: ?>
        <ul class="transaction-list">
            <?php foreach ($recent_transactions as $transaction): ?>
                <?php 
                $user = get_user_by_id($transaction['user_id']);
                $creator = get_user_by_id($transaction['created_by']);
                $is_cancelled = isset($transaction['cancelled']) && $transaction['cancelled'];
                ?>
                <li class="<?php echo $transaction['type'] . ($is_cancelled ? ' cancelled' : ''); ?>">
                    <strong><?php echo isset($user['display_name']) ? $user['display_name'] : $user['username']; ?></strong> 
                    <?php if ($transaction['type'] == 'malus'): ?>
                        received a <?php echo $transaction['sub_type']; ?> malus: 
                        <strong>-<?php echo format_currency($transaction['amount']); ?></strong>
                        - <?php echo $transaction['description']; ?>
                        <?php if ($is_cancelled): ?>
                            <em>(Cancelled with Jolly)</em>
                        <?php endif; ?>
                    <?php else: ?>
                        received a bonus: 
                        <strong>+<?php echo format_currency($transaction['amount']); ?></strong>
                        - <?php echo $transaction['description']; ?>
                    <?php endif; ?>
                    <br>
                    <small>
                        Added by <?php echo isset($creator['display_name']) ? $creator['display_name'] : $creator['username']; ?> on 
                        <?php echo date('d/m/Y H:i', is_numeric($transaction['timestamp']) ? $transaction['timestamp'] : strtotime($transaction['timestamp'])); ?>
                    </small>
                </li>
            <?php endforeach; ?>
        </ul>
        <a href="transactions.php" class="btn btn-accent">View All Transactions</a>
    <?php endif; ?>
</div>

<!-- Add Malus Modal -->
<div id="add-malus-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2><i class="fas fa-minus-circle"></i> Report Malus</h2>
        <form method="post" action="api/add_transaction.php" id="malus-form">
            <input type="hidden" name="type" value="malus">
            <div class="form-group">
                <label for="malus-user">User to Assign Malus</label>
                <select id="malus-user" name="user_id" class="form-control" onchange="updateMalusOptions()" required>
                    <option value="">Select a user</option>
                    <?php foreach ($demo_users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" data-display-name="<?php echo isset($user['display_name']) ? htmlspecialchars($user['display_name']) : htmlspecialchars($user['username']); ?>"><?php echo isset($user['display_name']) ? htmlspecialchars($user['display_name']) : htmlspecialchars($user['username']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="malus-type">Malus Type</label>
                <select id="malus-type" name="malus_type" class="form-control" onchange="updateMalusDescription()" required>
                    <?php foreach ($malus_types as $id => $malus): ?>
                        <option value="<?php echo $id; ?>"><?php echo $malus['name']; ?> (<?php echo format_currency($malus['amount']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="predefined-malus">Predefined Malus (Optional)</label>
                <select id="predefined-malus" class="form-control" onchange="setPredefinedMalus()">
                    <option value="">-- Select predefined malus or enter your own --</option>
                    <!-- Options will be populated by JavaScript -->
                </select>
            </div>
            <div class="form-group">
                <label for="malus-description">Description</label>
                <select id="malus-description" name="description" class="form-control" required>
                    <option value="">Select a predefined reason or enter custom</option>
                    <!-- This will be populated by JavaScript based on selected user -->
                </select>
                <input type="text" id="malus-custom-description" name="custom_description" class="form-control" placeholder="Enter a custom reason for this malus" style="margin-top: 5px; display: none;">
                <small id="malus-hint" style="color: gray; display: none;">Remember to enter a valid malus reason for this user.</small>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Submit Malus</button>
        </form>
        
        <!-- Alternative direct form for iOS devices -->
        <?php if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone') !== false || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad') !== false): ?>
        <div style="margin-top: 20px; border-top: 1px dashed #ccc; padding-top: 10px;">
            <h3><i class="fas fa-mobile-alt"></i> Alternative Method for iOS</h3>
            <p>If you have trouble assigning a malus, try this alternative form:</p>
            <form method="post" action="api/add_transaction.php" id="ios-malus-form">
                <input type="hidden" name="type" value="malus">
                
                <div class="form-group">
                    <label for="ios-malus-user">User:</label>
                    <select id="ios-malus-user" name="user_id" class="form-control" required>
                        <?php foreach ($demo_users as $user): ?>
                            <option value="<?php echo $user['id']; ?>"><?php echo isset($user['display_name']) ? htmlspecialchars($user['display_name']) : htmlspecialchars($user['username']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="ios-malus-type">Type:</label>
                    <select id="ios-malus-type" name="malus_type" class="form-control" required>
                        <?php foreach ($malus_types as $id => $malus): ?>
                            <option value="<?php echo $id; ?>"><?php echo $malus['name']; ?> (<?php echo format_currency($malus['amount']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="ios-malus-description">Description:</label>
                    <input type="text" id="ios-malus-description" name="description" class="form-control" placeholder="Enter reason" required>
                </div>
                
                <button type="submit" class="btn btn-accent">
                    <i class="fas fa-check"></i> Submit with iOS Method
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Bonus Modal -->
<div id="add-bonus-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2><i class="fas fa-plus-circle"></i> Add Bonus</h2>
        <form method="post" action="api/add_transaction.php" id="bonus-form">
            <input type="hidden" name="type" value="bonus">
            <div class="form-group">
                <label for="bonus-user">User to Assign Bonus</label>
                <select id="bonus-user" name="user_id" class="form-control" required>
                    <option value="">Select a user</option>
                    <?php foreach ($demo_users as $user): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo isset($user['display_name']) ? htmlspecialchars($user['display_name']) : htmlspecialchars($user['username']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="bonus-amount">Bonus Amount</label>
                <select id="bonus-amount" name="amount" class="form-control" required>
                    <?php foreach ($bonus_amounts as $amount): ?>
                        <option value="<?php echo $amount; ?>"><?php echo format_currency($amount); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="predefined-bonus">Predefined Bonus Reason (Optional)</label>
                <select id="predefined-bonus" class="form-control" onchange="setPredefinedBonus()">
                    <option value="">-- Select predefined reason or enter your own --</option>
                    <?php foreach ($positive_actions as $action): ?>
                        <option value="<?php echo htmlspecialchars($action); ?>"><?php echo htmlspecialchars($action); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="bonus-description">Description</label>
                <select id="bonus-description" name="description" class="form-control" required>
                    <option value="">Select a predefined reason</option>
                    <?php foreach ($positive_actions as $action): ?>
                        <option value="<?php echo htmlspecialchars($action); ?>"><?php echo htmlspecialchars($action); ?></option>
                    <?php endforeach; ?>
                    <option value="custom">Enter custom reason</option>
                </select>
                <input type="text" id="bonus-custom-description" name="custom_description" class="form-control" placeholder="Enter a custom reason for this bonus" style="margin-top: 5px; display: none;">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Submit Bonus</button>
        </form>
        
        <!-- Alternative direct form for iOS devices -->
        <?php if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone') !== false || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad') !== false): ?>
        <div style="margin-top: 20px; border-top: 1px dashed #ccc; padding-top: 10px;">
            <h3><i class="fas fa-mobile-alt"></i> Alternative Method for iOS</h3>
            <p>If you have trouble assigning a bonus, try this alternative form:</p>
            <form method="post" action="api/add_transaction.php" id="ios-bonus-form">
                <input type="hidden" name="type" value="bonus">
                
                <div class="form-group">
                    <label for="ios-bonus-user">User:</label>
                    <select id="ios-bonus-user" name="user_id" class="form-control" required>
                        <?php foreach ($demo_users as $user): ?>
                            <option value="<?php echo $user['id']; ?>"><?php echo isset($user['display_name']) ? htmlspecialchars($user['display_name']) : htmlspecialchars($user['username']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="ios-bonus-amount">Amount:</label>
                    <select id="ios-bonus-amount" name="amount" class="form-control" required>
                        <?php foreach ($bonus_amounts as $amount): ?>
                            <option value="<?php echo $amount; ?>"><?php echo format_currency($amount); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="ios-bonus-description">Description:</label>
                    <input type="text" id="ios-bonus-description" name="description" class="form-control" placeholder="Enter reason" required>
                </div>
                
                <button type="submit" class="btn btn-accent">
                    <i class="fas fa-check"></i> Submit with iOS Method
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Use Jolly Modal -->
<div id="use-jolly-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2><i class="fas fa-magic"></i> Use Jolly</h2>
        
        <?php if ($current_user['jolly_used']): ?>
            <div class="info-box">
                <i class="fas fa-info-circle"></i> You've already used your Jolly this month.
                <p>Wait until next month or ask an admin to reset your Jolly.</p>
            </div>
        <?php else: ?>
            <?php
            // Find malus transactions for the current user that haven't been cancelled
            $eligible_transactions = array_filter(get_user_transactions($current_user['id']), function($t) {
                return $t['type'] == 'malus' && $t['user_id'] == get_logged_in_user()['id'] && 
                       (!isset($t['cancelled']) || !$t['cancelled']);
            });
            ?>
            
            <?php if (empty($eligible_transactions)): ?>
                <div class="info-box">
                    <i class="fas fa-check-circle"></i> You don't have any malus to cancel.
                    <p>Good job! Your balance is clean.</p>
                </div>
            <?php else: ?>
                <p>Select a malus to cancel with your Jolly:</p>
                <ul class="transaction-list">
                    <?php foreach ($eligible_transactions as $transaction): ?>
                        <li class="malus">
                            <div>
                                <strong><?php echo $transaction['sub_type']; ?> malus: 
                                -<?php echo format_currency($transaction['amount']); ?></strong>
                                <p><?php echo htmlspecialchars($transaction['description']); ?></p>
                            </div>
                            <div>
                                <small><?php echo date('d/m/Y H:i', $transaction['timestamp']); ?></small>
                            </div>
                            <form method="post" action="api/use_jolly.php" class="jolly-form">
                                <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
                                <button type="submit" class="btn btn-accent" data-confirm="Are you sure you want to use your Jolly on this malus? You can only use one Jolly per month.">
                                    <i class="fas fa-magic"></i> Use Jolly
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="info-box">
                    <i class="fas fa-info-circle"></i> <strong>Remember:</strong> You can only use one Jolly per month!
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    // Detect if running on iOS
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    
    // JavaScript object with user-specific malus rules
    const userSpecificMalus = <?php echo json_encode($user_specific_malus); ?>;
    
    // Initialize bonus description dropdown
    document.addEventListener('DOMContentLoaded', function() {
        const bonusDescriptionSelect = document.getElementById('bonus-description');
        const bonusCustomDescription = document.getElementById('bonus-custom-description');
        
        if (bonusDescriptionSelect && bonusCustomDescription) {
            bonusDescriptionSelect.addEventListener('change', function() {
                if (bonusDescriptionSelect.value === 'custom') {
                    bonusCustomDescription.style.display = 'block';
                    bonusCustomDescription.required = true;
                } else {
                    bonusCustomDescription.style.display = 'none';
                    bonusCustomDescription.required = false;
                }
            });
        }
    });
    
    // Add direct form submit handlers for iOS
    if (isIOS) {
        console.log("iOS device detected, setting up direct form handlers");
        document.addEventListener('DOMContentLoaded', function() {
            // Add alternative button handlers for iOS devices
            const addMalusBtn = document.getElementById('add-malus-btn');
            const addBonusBtn = document.getElementById('add-bonus-btn');
            
            if (addMalusBtn) {
                addMalusBtn.addEventListener('click', function(e) {
                    console.log("iOS malus button clicked");
                });
            }
            
            if (addBonusBtn) {
                addBonusBtn.addEventListener('click', function(e) {
                    console.log("iOS bonus button clicked");
                });
            }
        });
    }
    
    // Function to update the malus options based on selected user
    function updateMalusOptions() {
        const userSelect = document.getElementById('malus-user');
        const predefinedSelect = document.getElementById('predefined-malus');
        const malusDescriptionSelect = document.getElementById('malus-description');
        const malusCustomDescription = document.getElementById('malus-custom-description');
        const malusHint = document.getElementById('malus-hint');
        
        // Clear existing options in the predefined dropdown except the default one
        while (predefinedSelect.options.length > 1) {
            predefinedSelect.remove(1);
        }
        
        // Clear existing options in the description dropdown except the first one
        malusDescriptionSelect.innerHTML = '<option value="">Select a predefined reason or enter custom</option>';
        
        if (userSelect.selectedIndex > 0 && userSelect.value) {
            // Get user display name to match against user-specific malus rules
            const selectedOption = userSelect.options[userSelect.selectedIndex];
            const userName = selectedOption.getAttribute('data-display-name');
            
            // Try to find malus rules for this user
            let userRules = null;
            
            // First try using the display name directly
            if (userName && userSpecificMalus[userName]) {
                userRules = userSpecificMalus[userName];
            } 
            // If not found, try using the option text
            else if (userSpecificMalus[selectedOption.text]) {
                userRules = userSpecificMalus[selectedOption.text];
            }
            // If still not found, try a case-insensitive search
            else {
                for (const ruleName in userSpecificMalus) {
                    if (ruleName.toLowerCase() === userName?.toLowerCase() || 
                        ruleName.toLowerCase() === selectedOption.text.toLowerCase()) {
                        userRules = userSpecificMalus[ruleName];
                        break;
                    }
                }
            }
            
            if (userRules) {
                // Add Malus #1 if defined
                if (userRules.malus1 && userRules.malus1.trim() !== '') {
                    // Add to predefined select for backward compatibility
                    const option1 = document.createElement('option');
                    option1.value = userRules.malus1;
                    option1.textContent = 'Malus #1: ' + userRules.malus1;
                    predefinedSelect.appendChild(option1);
                    
                    // Add to main description dropdown
                    const descOption1 = document.createElement('option');
                    descOption1.value = userRules.malus1;
                    descOption1.textContent = userRules.malus1;
                    malusDescriptionSelect.appendChild(descOption1);
                }
                
                // Add Malus #2 if defined
                if (userRules.malus2 && userRules.malus2.trim() !== '') {
                    // Add to predefined select for backward compatibility
                    const option2 = document.createElement('option');
                    option2.value = userRules.malus2;
                    option2.textContent = 'Malus #2: ' + userRules.malus2;
                    predefinedSelect.appendChild(option2);
                    
                    // Add to main description dropdown
                    const descOption2 = document.createElement('option');
                    descOption2.value = userRules.malus2;
                    descOption2.textContent = userRules.malus2;
                    malusDescriptionSelect.appendChild(descOption2);
                }
                
                // Add Extra Malus if defined
                if (userRules.extra && userRules.extra.trim() !== '') {
                    // Add to predefined select for backward compatibility
                    const option3 = document.createElement('option');
                    option3.value = userRules.extra;
                    option3.textContent = 'Extra Malus: ' + userRules.extra;
                    predefinedSelect.appendChild(option3);
                    
                    // Add to main description dropdown
                    const descOption3 = document.createElement('option');
                    descOption3.value = userRules.extra;
                    descOption3.textContent = userRules.extra;
                    malusDescriptionSelect.appendChild(descOption3);
                }
                
                // Show hint if at least one predefined malus exists
                if (predefinedSelect.options.length > 1) {
                    malusHint.style.display = 'block';
                    malusHint.textContent = 'This user has predefined malus reasons. You can select one or enter your own.';
                } else {
                    malusHint.style.display = 'block';
                    malusHint.textContent = 'This user has predefined malus, but they are not properly configured.';
                }
            } else {
                malusHint.style.display = 'block';
                malusHint.textContent = 'No predefined malus for this user. Enter a custom reason.';
            }
        } else {
            malusHint.style.display = 'none';
        }
        
        // Always add custom option
        const customOption = document.createElement('option');
        customOption.value = 'custom';
        customOption.textContent = 'Enter custom reason';
        malusDescriptionSelect.appendChild(customOption);
        
        // Add handler for the description dropdown
        malusDescriptionSelect.onchange = function() {
            if (this.value === 'custom') {
                malusCustomDescription.style.display = 'block';
                malusCustomDescription.required = true;
            } else {
                malusCustomDescription.style.display = 'none';
                malusCustomDescription.required = false;
            }
        };
        
        // Update the malus type and description based on selection
        updateMalusDescription();
    }
    
    // Function to update the malus description field based on selected predefined malus
    function setPredefinedMalus() {
        const predefinedSelect = document.getElementById('predefined-malus');
        const descriptionSelect = document.getElementById('malus-description');
        const customDescription = document.getElementById('malus-custom-description');
        
        if (predefinedSelect.selectedIndex > 0) {
            // Find if the option exists in the dropdown
            let optionExists = false;
            for (let i = 0; i < descriptionSelect.options.length; i++) {
                if (descriptionSelect.options[i].value === predefinedSelect.value) {
                    descriptionSelect.selectedIndex = i;
                    optionExists = true;
                    break;
                }
            }
            
            // If not found, select custom and fill the input
            if (!optionExists) {
                // Find the 'custom' option
                for (let i = 0; i < descriptionSelect.options.length; i++) {
                    if (descriptionSelect.options[i].value === 'custom') {
                        descriptionSelect.selectedIndex = i;
                        break;
                    }
                }
                
                // Display and populate the custom input
                customDescription.style.display = 'block';
                customDescription.required = true;
                customDescription.value = predefinedSelect.value;
            } else {
                // Hide the custom input
                customDescription.style.display = 'none';
                customDescription.required = false;
            }
        }
    }
    
    // Function to update malus type based on predefined malus selection
    function updateMalusDescription() {
        const malusTypeSelect = document.getElementById('malus-type');
        const predefinedSelect = document.getElementById('predefined-malus');
        
        // If predefined malus selected, choose the appropriate malus type
        if (predefinedSelect.selectedIndex > 0) {
            const optionText = predefinedSelect.options[predefinedSelect.selectedIndex].textContent;
            
            if (optionText.startsWith('Malus #1:')) {
                malusTypeSelect.value = '1';
            } else if (optionText.startsWith('Malus #2:')) {
                malusTypeSelect.value = '2';
            } else if (optionText.startsWith('Extra Malus:')) {
                malusTypeSelect.value = '3';
            }
        }
    }
    
    // Function to set bonus description from predefined list
    function setPredefinedBonus() {
        const predefinedSelect = document.getElementById('predefined-bonus');
        const descriptionSelect = document.getElementById('bonus-description');
        const customDescription = document.getElementById('bonus-custom-description');
        
        if (predefinedSelect.selectedIndex > 0) {
            // Find if the option exists in the dropdown
            let optionExists = false;
            for (let i = 0; i < descriptionSelect.options.length; i++) {
                if (descriptionSelect.options[i].value === predefinedSelect.value) {
                    descriptionSelect.selectedIndex = i;
                    optionExists = true;
                    break;
                }
            }
            
            // If not found, select custom and fill the input
            if (!optionExists) {
                // Find the 'custom' option
                for (let i = 0; i < descriptionSelect.options.length; i++) {
                    if (descriptionSelect.options[i].value === 'custom') {
                        descriptionSelect.selectedIndex = i;
                        break;
                    }
                }
                
                // Display and populate the custom input
                customDescription.style.display = 'block';
                customDescription.required = true;
                customDescription.value = predefinedSelect.value;
            } else {
                // Hide the custom input
                customDescription.style.display = 'none';
                customDescription.required = false;
            }
        }
    }
    
    // Form validation and submission with debug info
    document.getElementById('malus-form').addEventListener('submit', function(e) {
        const userSelect = document.getElementById('malus-user');
        const descriptionSelect = document.getElementById('malus-description');
        const customDescription = document.getElementById('malus-custom-description');
        const malusTypeSelect = document.getElementById('malus-type');
        
        // Debug form submission
        console.log("Submitting malus form");
        console.log("- User ID:", userSelect.value);
        console.log("- Malus Type:", malusTypeSelect.value);
        console.log("- Description:", descriptionSelect.value);
        
        if (userSelect.value === '') {
            alert('Please select a user to assign the malus');
            e.preventDefault();
            return false;
        }
        
        if (malusTypeSelect.value === '') {
            alert('Please select a malus type');
            e.preventDefault();
            return false;
        }
        
        if (descriptionSelect.value === '') {
            alert('Please select or enter a description for the malus');
            e.preventDefault();
            return false;
        }
        
        // If custom description is selected but empty
        if (descriptionSelect.value === 'custom' && customDescription.value.trim() === '') {
            alert('Please enter a custom description for the malus');
            e.preventDefault();
            return false;
        }
        
        // If using custom description, create a hidden field with the custom value
        if (descriptionSelect.value === 'custom') {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'description';
            hiddenInput.value = customDescription.value;
            this.appendChild(hiddenInput);
        }
        
        // Set a cookie to confirm the form was submitted
        document.cookie = "malus_submitted=true; path=/; max-age=30";
        
        return true;
    });
    
    document.getElementById('bonus-form').addEventListener('submit', function(e) {
        const userSelect = document.getElementById('bonus-user');
        const amountSelect = document.getElementById('bonus-amount');
        const descriptionSelect = document.getElementById('bonus-description');
        const customDescription = document.getElementById('bonus-custom-description');
        
        // Debug form submission
        console.log("Submitting bonus form");
        console.log("- User ID:", userSelect.value);
        console.log("- Bonus Amount:", amountSelect.value);
        console.log("- Description:", descriptionSelect.value);
        
        if (userSelect.value === '') {
            alert('Please select a user to assign the bonus');
            e.preventDefault();
            return false;
        }
        
        if (amountSelect.value === '') {
            alert('Please select a bonus amount');
            e.preventDefault();
            return false;
        }
        
        if (descriptionSelect.value === '') {
            alert('Please select or enter a description for the bonus');
            e.preventDefault();
            return false;
        }
        
        // If custom description is selected but empty
        if (descriptionSelect.value === 'custom' && customDescription.value.trim() === '') {
            alert('Please enter a custom description for the bonus');
            e.preventDefault();
            return false;
        }
        
        // If using custom description, create a hidden field with the custom value
        if (descriptionSelect.value === 'custom') {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'description';
            hiddenInput.value = customDescription.value;
            this.appendChild(hiddenInput);
        }
        
        // Set a cookie to confirm the form was submitted
        document.cookie = "bonus_submitted=true; path=/; max-age=30";
        
        return true;
    });
    
    // Add a global debug function
    window.submitDebugInfo = function() {
        const debugInfo = {
            url: window.location.href,
            userAgent: navigator.userAgent,
            screen: {
                width: window.screen.width,
                height: window.screen.height
            },
            modalState: {},
            formValues: {}
        };
        
        // Get modal states
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            debugInfo.modalState[modal.id] = modal.style.display;
        });
        
        // Get form values for debugging
        const malusForm = document.getElementById('malus-form');
        const bonusForm = document.getElementById('bonus-form');
        
        if (malusForm) {
            const formData = new FormData(malusForm);
            const formValues = {};
            for (const [key, value] of formData.entries()) {
                formValues[key] = value;
            }
            debugInfo.formValues.malus = formValues;
        }
        
        if (bonusForm) {
            const formData = new FormData(bonusForm);
            const formValues = {};
            for (const [key, value] of formData.entries()) {
                formValues[key] = value;
            }
            debugInfo.formValues.bonus = formValues;
        }
        
        console.log("Debug info:", debugInfo);
        alert("Debug information has been logged to the console");
    };
</script>

<?php include 'includes/footer.php'; ?>