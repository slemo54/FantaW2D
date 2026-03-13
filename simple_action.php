<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require login
require_login();

// Basic HTML structure (minimal for compatibility)
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fanta W2D - Simple Action</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f9fa;
            color: #333;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #3498db;
            margin-top: 0;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        select, input, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 20px;
            font-weight: bold;
        }
        .button-secondary {
            background-color: #95a5a6;
        }
        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        a {
            color: #3498db;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Fanta W2D</h1>
        
        <?php
        // Get action type
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        $target_id = isset($_GET['target_id']) ? intval($_GET['target_id']) : 0;
        
        if ($action === 'malus') {
            // Display simplified malus form
            ?>
            <h2>Report Malus</h2>
            <form method="post" action="api/add_transaction.php">
                <input type="hidden" name="type" value="malus">
                
                <label for="user_id">User:</label>
                <select name="user_id" id="user_id" required>
                    <option value="">Select a user</option>
                    <?php foreach ($demo_users as $user): ?>
                        <?php if ($user['id'] != 1): // Skip admin ?>
                            <option value="<?php echo $user['id']; ?>" 
                                <?php echo ($target_id && $user['id'] == $target_id) ? 'selected' : ''; ?>>
                                <?php echo isset($user['display_name']) ? htmlspecialchars($user['display_name']) : htmlspecialchars($user['username']); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                
                <label for="malus_type">Malus Type:</label>
                <select name="malus_type" id="malus_type" required>
                    <?php foreach ($malus_types as $id => $malus): ?>
                        <option value="<?php echo $id; ?>"><?php echo $malus['name']; ?> (<?php echo format_currency($malus['amount']); ?>)</option>
                    <?php endforeach; ?>
                </select>
                
                <label for="description">Description:</label>
                <select name="description" id="description" required>
                    <option value="">Select a predefined reason or enter custom</option>
                    <!-- This will be populated via JavaScript based on selected user -->
                </select>
                <input type="text" name="custom_description" id="custom-description" placeholder="Enter custom reason for malus" style="margin-top: 5px; display: none;">
                
                <button type="submit">Submit Malus</button>
            </form>
            <?php
        } elseif ($action === 'bonus') {
            // Display simplified bonus form
            ?>
            <h2>Add Bonus</h2>
            <form method="post" action="api/add_transaction.php">
                <input type="hidden" name="type" value="bonus">
                
                <label for="user_id">User:</label>
                <select name="user_id" id="user_id" required>
                    <option value="">Select a user</option>
                    <?php foreach ($demo_users as $user): ?>
                        <?php if ($user['id'] != 1): // Skip admin ?>
                            <option value="<?php echo $user['id']; ?>"
                                <?php echo ($target_id && $user['id'] == $target_id) ? 'selected' : ''; ?>>
                                <?php echo isset($user['display_name']) ? htmlspecialchars($user['display_name']) : htmlspecialchars($user['username']); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                
                <label for="amount">Bonus Amount:</label>
                <select name="amount" id="amount" required>
                    <?php foreach ($bonus_amounts as $amount): ?>
                        <option value="<?php echo $amount; ?>"><?php echo format_currency($amount); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <label for="description">Description:</label>
                <select name="description" id="description" required>
                    <option value="">Select a predefined reason</option>
                    <?php foreach ($positive_actions as $action): ?>
                        <option value="<?php echo htmlspecialchars($action); ?>"><?php echo htmlspecialchars($action); ?></option>
                    <?php endforeach; ?>
                    <option value="custom">Enter custom reason</option>
                </select>
                <input type="text" name="custom_description" id="custom-description" placeholder="Enter custom reason for bonus" style="margin-top: 5px; display: none;">
                
                <button type="submit">Submit Bonus</button>
            </form>
            <?php
        } else {
            // Display options
            ?>
            <h2>Choose Action</h2>
            <div class="actions">
                <a href="simple_action.php?action=malus" style="flex: 1;">
                    <button>Report Malus</button>
                </a>
                <a href="simple_action.php?action=bonus" style="flex: 1;">
                    <button>Add Bonus</button>
                </a>
            </div>
            <?php
        }
        ?>
        
        <div style="margin-top: 20px; text-align: center;">
            <a href="dashboard.php">Back to Dashboard</a>
        </div>
    </div>

    <script>
    // Define user-specific malus in JavaScript
    const userSpecificMalus = <?php echo json_encode($user_specific_malus); ?>;

    document.addEventListener('DOMContentLoaded', function() {
        // Get elements for malus form
        const userSelect = document.getElementById('user_id');
        const descriptionSelect = document.getElementById('description');
        const customDescription = document.getElementById('custom-description');
        
        // Handler for user selection change to populate malus reasons
        if (userSelect && descriptionSelect && window.location.href.includes('action=malus')) {
            userSelect.addEventListener('change', function() {
                updateMalusOptions();
            });
            
            // Initial update if a user is selected
            if (userSelect.value) {
                updateMalusOptions();
            }
            
            // Handle custom description toggle
            descriptionSelect.addEventListener('change', function() {
                if (descriptionSelect.value === 'custom') {
                    customDescription.style.display = 'block';
                    customDescription.required = true;
                } else {
                    customDescription.style.display = 'none';
                    customDescription.required = false;
                }
            });
        }
        
        // Handler for bonus form
        if (descriptionSelect && customDescription && window.location.href.includes('action=bonus')) {
            descriptionSelect.addEventListener('change', function() {
                if (descriptionSelect.value === 'custom') {
                    customDescription.style.display = 'block';
                    customDescription.required = true;
                } else {
                    customDescription.style.display = 'none';
                    customDescription.required = false;
                }
            });
        }
        
        // Handle form submission
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (descriptionSelect.value === 'custom') {
                    if (customDescription.value.trim() === '') {
                        e.preventDefault();
                        alert('Please enter a custom description');
                        return false;
                    }
                    
                    // Use the custom description value
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'description';
                    hiddenInput.value = customDescription.value;
                    form.appendChild(hiddenInput);
                }
            });
        }
        
        // Function to update malus options based on selected user
        function updateMalusOptions() {
            const selectedUserName = userSelect.options[userSelect.selectedIndex].text;
            descriptionSelect.innerHTML = '<option value="">Select a predefined reason or enter custom</option>';
            
            if (userSpecificMalus[selectedUserName]) {
                const userMalus = userSpecificMalus[selectedUserName];
                
                // Add malus1 if exists
                if (userMalus.malus1 && userMalus.malus1.trim() !== '') {
                    const option = document.createElement('option');
                    option.value = userMalus.malus1;
                    option.textContent = userMalus.malus1;
                    descriptionSelect.appendChild(option);
                }
                
                // Add malus2 if exists
                if (userMalus.malus2 && userMalus.malus2.trim() !== '') {
                    const option = document.createElement('option');
                    option.value = userMalus.malus2;
                    option.textContent = userMalus.malus2;
                    descriptionSelect.appendChild(option);
                }
                
                // Add extra if exists
                if (userMalus.extra && userMalus.extra.trim() !== '') {
                    const option = document.createElement('option');
                    option.value = userMalus.extra;
                    option.textContent = userMalus.extra;
                    descriptionSelect.appendChild(option);
                }
            }
            
            // Always add custom option
            const customOption = document.createElement('option');
            customOption.value = 'custom';
            customOption.textContent = 'Enter custom reason';
            descriptionSelect.appendChild(customOption);
        }
    });
    </script>
</body>
</html>