<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Debug della sessione
echo "<h1>Debug Sessione</h1>";
echo "<pre>";
echo "SESSION:\n";
print_r($_SESSION);
echo "\n\n";

echo "Current User ID: " . $_SESSION['user_id'] . "\n\n";

// Debug degli utenti nella sessione
if (isset($_SESSION['users'])) {
    echo "Users in SESSION:\n";
    foreach ($_SESSION['users'] as $user) {
        echo "ID: " . $user['id'] . ", Username: " . $user['username'] . ", Display: " . $user['display_name'] . "\n";
    }
} else {
    echo "No users in SESSION\n";
}
echo "\n\n";

// Debug degli utenti in $demo_users
echo "Users in \$demo_users:\n";
foreach ($demo_users as $user) {
    echo "ID: " . $user['id'] . ", Username: " . $user['username'] . ", Display: " . $user['display_name'] . "\n";
}
echo "\n\n";

// Debug dell'utente attuale
$current_user = get_logged_in_user();
echo "Current User:\n";
print_r($current_user);
echo "</pre>";

// Link per tornare alla dashboard
echo "<p><a href='dashboard.php'>Vai alla dashboard normale</a></p>";
echo "<p><a href='simple_login.php'>Torna al login semplificato</a></p>";
?>