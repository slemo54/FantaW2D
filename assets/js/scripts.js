document.addEventListener('DOMContentLoaded', function() {
    // Detect iOS
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    if (isIOS) {
        document.body.classList.add('ios-device');
    }
    
    // Initialize modals
    const modals = document.querySelectorAll('.modal');
    const modalTriggers = document.querySelectorAll('[data-modal]');
    const closeButtons = document.querySelectorAll('.close');
    
    // Helper function to open modal
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        // For iOS, add a class to prevent background scrolling
        if (isIOS) {
            document.body.classList.add('modal-open');
        }
        
        modal.style.display = 'block';
        
        // Ensure modals work properly on iOS
        if (isIOS) {
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';
        }
    }
    
    // Helper function to close modal
    function closeModal(modal) {
        if (!modal) return;
        
        modal.style.display = 'none';
        
        // Remove iOS specific classes/styles
        if (isIOS) {
            document.body.classList.remove('modal-open');
            document.body.style.position = '';
            document.body.style.width = '';
        }
    }
    
    // Open modal when trigger is clicked
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent default button behavior
            const modalId = trigger.getAttribute('data-modal');
            openModal(modalId);
        });
    });
    
    // Close modal when close button is clicked
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            closeModal(modal);
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', (event) => {
        modals.forEach(modal => {
            if (event.target === modal) {
                closeModal(modal);
            }
        });
    });
    
    // Close modal when pressing Escape key
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            modals.forEach(modal => {
                if (modal.style.display === 'block') {
                    closeModal(modal);
                }
            });
        }
    });
    
    // Auto-hide notifications after animation completes
    const notification = document.querySelector('.notification');
    if (notification) {
        notification.addEventListener('animationend', () => {
            notification.style.display = 'none';
        });
    }
    
    // Confirm dangerous actions
    const dangerousActions = document.querySelectorAll('[data-confirm]');
    dangerousActions.forEach(action => {
        action.addEventListener('click', (event) => {
            const confirmMessage = action.getAttribute('data-confirm');
            if (!confirm(confirmMessage)) {
                event.preventDefault();
            }
        });
    });
    
    // Fix iOS input focusing issues
    if (isIOS) {
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('touchstart', (e) => {
                // Prevents zooming on iOS
                e.target.style.fontSize = '16px';
            });
        });
    }
});