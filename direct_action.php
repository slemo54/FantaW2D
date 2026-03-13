<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require login
require_login();

$current_user = get_logged_in_user();
$action = isset($_GET['action']) ? $_GET['action'] : '';
$target_id = isset($_GET['target_id']) ? intval($_GET['target_id']) : 0;

// Get all users for the form
$all_users = $demo_users;

// Include header
include 'includes/header.php';
?>

<div class="container">
    <?php if ($action === 'malus'): ?>
        <div class="card">
            <h2><i class="fas fa-minus-circle"></i> Report Malus</h2>
            <form method="post" action="api/add_transaction.php" id="direct-malus-form">
                <input type="hidden" name="type" value="malus">
                
                <div class="form-group">
                    <label for="malus-user">User to Assign Malus</label>
                    <select id="malus-user" name="user_id" class="form-control" required>
                        <option value="">Select a user</option>
                        <?php foreach ($all_users as $user): ?>
                            <?php if ($user['id'] != 1): // Skip admin ?>
                                <option value="<?php echo $user['id']; ?>" 
                                    <?php echo ($target_id && $user['id'] == $target_id) ? 'selected' : ''; ?>>
                                    <?php echo isset($user['display_name']) ? htmlspecialchars($user['display_name']) : htmlspecialchars($user['username']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="malus-type">Malus Type</label>
                    <select id="malus-type" name="malus_type" class="form-control" required>
                        <?php foreach ($malus_types as $id => $malus): ?>
                            <option value="<?php echo $id; ?>"><?php echo $malus['name']; ?> (<?php echo format_currency($malus['amount']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="malus-description">Description</label>
                    <select id="malus-description" name="description" class="form-control" required>
                        <option value="">Select a predefined reason or enter custom</option>
                        <!-- This will be populated via JavaScript based on selected user -->
                    </select>
                    <input type="text" id="malus-custom-description" name="custom_description" class="form-control" placeholder="Enter a custom reason for this malus" style="margin-top: 5px; display: none;">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Submit Malus
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    <?php elseif ($action === 'bonus'): ?>
        <div class="card">
            <h2><i class="fas fa-plus-circle"></i> Add Bonus</h2>
            <form method="post" action="api/add_transaction.php" id="direct-bonus-form">
                <input type="hidden" name="type" value="bonus">
                
                <div class="form-group">
                    <label for="bonus-user">User to Assign Bonus</label>
                    <select id="bonus-user" name="user_id" class="form-control" required>
                        <option value="">Select a user</option>
                        <?php foreach ($all_users as $user): ?>
                            <?php if ($user['id'] != 1): // Skip admin ?>
                                <option value="<?php echo $user['id']; ?>"
                                    <?php echo ($target_id && $user['id'] == $target_id) ? 'selected' : ''; ?>>
                                    <?php echo isset($user['display_name']) ? htmlspecialchars($user['display_name']) : htmlspecialchars($user['username']); ?>
                                </option>
                            <?php endif; ?>
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
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Submit Bonus
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="card">
            <h2>Choose Action</h2>
            <div class="quick-actions">
                <a href="direct_action.php?action=malus" class="btn btn-secondary">
                    <i class="fas fa-minus-circle"></i> Report Malus
                </a>
                <a href="direct_action.php?action=bonus" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add Bonus
                </a>
            </div>
            <div style="margin-top: 20px;">
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Define user-specific malus in JavaScript
const userSpecificMalus = <?php echo json_encode($user_specific_malus); ?>;

// Handle malus user selection
document.addEventListener('DOMContentLoaded', function() {
    const malusUserSelect = document.getElementById('malus-user');
    const malusDescriptionSelect = document.getElementById('malus-description');
    const malusCustomDescription = document.getElementById('malus-custom-description');
    
    if (malusUserSelect && malusDescriptionSelect) {
        malusUserSelect.addEventListener('change', function() {
            updateMalusOptions();
        });
        
        // Initial update
        if (malusUserSelect.value) {
            updateMalusOptions();
        }
    }
    
    // Handle bonus custom description
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
    
    // Handle form submission
    const directMalusForm = document.getElementById('direct-malus-form');
    const directBonusForm = document.getElementById('direct-bonus-form');
    
    if (directMalusForm) {
        directMalusForm.addEventListener('submit', function(e) {
            if (malusDescriptionSelect.value === 'custom' && malusCustomDescription.value.trim() === '') {
                e.preventDefault();
                alert('Please enter a custom description');
                return false;
            } else if (malusDescriptionSelect.value === 'custom') {
                // Use the custom description value
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'description';
                hiddenInput.value = malusCustomDescription.value;
                directMalusForm.appendChild(hiddenInput);
            }
        });
    }
    
    if (directBonusForm) {
        directBonusForm.addEventListener('submit', function(e) {
            if (bonusDescriptionSelect.value === 'custom' && bonusCustomDescription.value.trim() === '') {
                e.preventDefault();
                alert('Please enter a custom description');
                return false;
            } else if (bonusDescriptionSelect.value === 'custom') {
                // Use the custom description value
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'description';
                hiddenInput.value = bonusCustomDescription.value;
                directBonusForm.appendChild(hiddenInput);
            }
        });
    }
    
    // Function to update malus options based on selected user
    function updateMalusOptions() {
        const selectedUserName = malusUserSelect.options[malusUserSelect.selectedIndex].text;
        malusDescriptionSelect.innerHTML = '<option value="">Select a predefined reason or enter custom</option>';
        
        if (userSpecificMalus[selectedUserName]) {
            const userMalus = userSpecificMalus[selectedUserName];
            
            // Add malus1 if exists
            if (userMalus.malus1 && userMalus.malus1.trim() !== '') {
                const option = document.createElement('option');
                option.value = userMalus.malus1;
                option.textContent = userMalus.malus1;
                malusDescriptionSelect.appendChild(option);
            }
            
            // Add malus2 if exists
            if (userMalus.malus2 && userMalus.malus2.trim() !== '') {
                const option = document.createElement('option');
                option.value = userMalus.malus2;
                option.textContent = userMalus.malus2;
                malusDescriptionSelect.appendChild(option);
            }
            
            // Add extra if exists
            if (userMalus.extra && userMalus.extra.trim() !== '') {
                const option = document.createElement('option');
                option.value = userMalus.extra;
                option.textContent = userMalus.extra;
                malusDescriptionSelect.appendChild(option);
            }
        }
        
        // Always add custom option
        const customOption = document.createElement('option');
        customOption.value = 'custom';
        customOption.textContent = 'Enter custom reason';
        malusDescriptionSelect.appendChild(customOption);
        
        // Handle description type
        malusDescriptionSelect.addEventListener('change', function() {
            if (malusDescriptionSelect.value === 'custom') {
                malusCustomDescription.style.display = 'block';
                malusCustomDescription.required = true;
            } else {
                malusCustomDescription.style.display = 'none';
                malusCustomDescription.required = false;
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>