<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require login
require_login();

$current_user = get_logged_in_user();

// Get all transactions for current user
$transactions = get_user_transactions($current_user['id']);

include 'includes/header.php';
?>

<div class="card">
    <h2>Transaction History</h2>
    
    <!-- Search and Filter Controls -->
    <div class="filter-controls">
        <div class="search-box">
            <input type="text" id="transaction-search" placeholder="Search transactions..." class="form-control">
            <i class="fas fa-search"></i>
        </div>
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="malus">Malus</button>
            <button class="filter-btn" data-filter="bonus">Bonus</button>
        </div>
    </div>
    
    <?php if (empty($transactions)): ?>
        <p>No transactions yet.</p>
    <?php else: ?>
        <div class="transactions-container">
            <ul class="transaction-list" id="transaction-list">
                <?php foreach ($transactions as $transaction): ?>
                    <?php 
                    $user = get_user_by_id($transaction['user_id']);
                    $creator = get_user_by_id($transaction['created_by']);
                    $is_cancelled = isset($transaction['cancelled']) && $transaction['cancelled'];
                    ?>
                    <li class="transaction-item <?php echo $transaction['type'] . ($is_cancelled ? ' cancelled' : ''); ?>" 
                        data-type="<?php echo $transaction['type']; ?>"
                        data-user="<?php echo isset($user['display_name']) ? strtolower($user['display_name']) : strtolower($user['username']); ?>"
                        data-description="<?php echo strtolower($transaction['description']); ?>">
                        
                        <div class="transaction-header">
                            <div class="transaction-user">
                                <i class="fas fa-user-circle"></i>
                                <strong><?php echo isset($user['display_name']) ? $user['display_name'] : $user['username']; ?></strong>
                            </div>
                            <div class="transaction-amount <?php echo $transaction['type'] == 'malus' ? 'negative' : 'positive'; ?>">
                                <?php if ($transaction['type'] == 'malus'): ?>
                                    -<?php echo format_currency($transaction['amount']); ?>
                                <?php else: ?>
                                    +<?php echo format_currency($transaction['amount']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="transaction-details">
                            <?php if ($transaction['type'] == 'malus'): ?>
                                <div class="transaction-type">
                                    <i class="fas fa-minus-circle"></i> <?php echo $transaction['sub_type']; ?> malus
                                </div>
                                <div class="transaction-description">
                                    <?php echo $transaction['description']; ?>
                                    <?php if ($is_cancelled): ?>
                                        <span class="cancelled-badge"><i class="fas fa-magic"></i> Cancelled with Jolly</span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="transaction-type">
                                    <i class="fas fa-plus-circle"></i> Bonus
                                </div>
                                <div class="transaction-description">
                                    <?php echo $transaction['description']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="transaction-footer">
                            <small>
                                <i class="fas fa-user"></i> <?php echo isset($creator['display_name']) ? $creator['display_name'] : $creator['username']; ?> |
                                <i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', is_numeric($transaction['timestamp']) ? $transaction['timestamp'] : strtotime($transaction['timestamp'])); ?>
                            </small>
                            
                            <?php if (is_admin($current_user) && !$is_cancelled): ?>
                            <button class="btn-sm <?php echo $transaction['type'] == 'malus' ? 'btn-secondary' : 'btn-danger'; ?> cancel-transaction" 
                                   data-id="<?php echo $transaction['id']; ?>"
                                   data-type="<?php echo $transaction['type']; ?>">
                                <i class="fas fa-times-circle"></i> Cancel <?php echo ucfirst($transaction['type']); ?>
                            </button>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <div class="no-results" style="display: none;">
                <i class="fas fa-search"></i>
                <p>No transactions match your search</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Transaction filtering and search
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('transaction-search');
    const transactionList = document.getElementById('transaction-list');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const noResults = document.querySelector('.no-results');
    
    if(!searchInput || !transactionList) return;
    
    // Filter buttons
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            filterTransactions(filter, searchInput.value.toLowerCase());
        });
    });
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const activeFilter = document.querySelector('.filter-btn.active').getAttribute('data-filter');
        filterTransactions(activeFilter, this.value.toLowerCase());
    });
    
    function filterTransactions(typeFilter, searchQuery) {
        const items = transactionList.querySelectorAll('.transaction-item');
        let hasVisibleItems = false;
        
        items.forEach(item => {
            const type = item.getAttribute('data-type');
            const user = item.getAttribute('data-user');
            const description = item.getAttribute('data-description');
            
            // Check if item matches type filter
            const matchesType = typeFilter === 'all' || type === typeFilter;
            
            // Check if item matches search query
            const matchesSearch = user.includes(searchQuery) || 
                                 description.includes(searchQuery);
            
            // Show or hide item based on filters
            if (matchesType && matchesSearch) {
                item.style.display = 'block';
                hasVisibleItems = true;
            } else {
                item.style.display = 'none';
            }
        });
        
        // Show or hide "no results" message
        noResults.style.display = hasVisibleItems ? 'none' : 'block';
    }
    
    // Admin: Cancel transaction buttons
    const cancelButtons = document.querySelectorAll('.cancel-transaction');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            const transactionId = this.getAttribute('data-id');
            const transactionType = this.getAttribute('data-type');
            let message = '';
            
            if (transactionType === 'malus') {
                message = 'Are you sure you want to cancel this malus transaction? The amount will be refunded to the user.';
            } else if (transactionType === 'bonus') {
                message = 'Are you sure you want to cancel this bonus transaction? The amount will be deducted from the user\'s balance.';
            }
            
            message += ' This action cannot be undone.';
            
            if (confirm(message)) {
                // Show loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
                this.disabled = true;
                
                // Submit form to cancel transaction
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'api/use_jolly.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'transaction_id';
                input.value = transactionId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>