        <footer>
            &copy; <?php echo date('Y'); ?> <?php echo $app_name; ?> - Version <?php echo $app_version; ?>
        </footer>
    </div> <!-- End of container -->
    
    <!-- Modal functionality -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal functionality
        var modals = document.querySelectorAll('.modal');
        var modalTriggers = document.querySelectorAll('[data-modal]');
        var closeBtns = document.querySelectorAll('.close');
        
        // Open modal when trigger is clicked
        modalTriggers.forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                var modalId = this.getAttribute('data-modal');
                var modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'block';
                    // Add class to body to prevent scrolling
                    document.body.classList.add('modal-open');
                }
            });
        });
        
        // Close modal when close button is clicked
        closeBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var modal = this.closest('.modal');
                modal.style.display = 'none';
                // Remove class from body to allow scrolling
                document.body.classList.remove('modal-open');
            });
        });
        
        // Close modal when clicking outside of modal content
        window.addEventListener('click', function(event) {
            modals.forEach(function(modal) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                    // Remove class from body to allow scrolling
                    document.body.classList.remove('modal-open');
                }
            });
        });
        
        // Handle auto-fadeout of notifications
        var notification = document.querySelector('.notification');
        if (notification) {
            setTimeout(function() {
                notification.style.display = 'none';
            }, 5000);
        }
        
        // Add confirmation dialog to any button with data-confirm attribute
        var confirmBtns = document.querySelectorAll('[data-confirm]');
        confirmBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                var message = this.getAttribute('data-confirm');
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    });
    
    // Service Worker Registration for PWA
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                })
                .catch(function(error) {
                    console.log('ServiceWorker registration failed: ', error);
                });
        });
    }
    </script>
</body>
</html>