<?php
// Carica le configurazioni originali
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Inizializza i dati demo
initialize_demo_data();

// Funzione per il login diretto
function direct_login($username) {
    global $demo_users;
    
    // Normalizza username (tutto minuscolo)
    $username = strtolower($username);
    
    // Mappa per nomi alternativi
    $name_map = [
        'anselmo' => 'anselmoacquah',
        'acquah' => 'anselmoacquah',
        'davide' => 'davidezanella',
        'zanella' => 'davidezanella',
        'beatrice' => 'beatricemotterle',
        'motterle' => 'beatricemotterle',
        'andrea' => 'andreacariglia',
        'cariglia' => 'andreacariglia'
    ];
    
    // Se il nome è nella mappa, usa la versione mappata
    if (isset($name_map[$username])) {
        $username = $name_map[$username];
    }
    
    echo "<h2>Tentativo di login per: " . htmlspecialchars($username) . "</h2>";
    
    // Cerca l'utente
    $found_user = null;
    foreach ($demo_users as $user) {
        if (strtolower($user['username']) === $username) {
            $found_user = $user;
            break;
        }
    }
    
    if ($found_user) {
        // Login riuscito
        $_SESSION['user_id'] = $found_user['id'];
        
        echo "<div style='color:green; font-weight:bold;'>Login riuscito!</div>";
        echo "<p>Utente: " . htmlspecialchars($found_user['display_name']) . " (ID: " . $found_user['id'] . ")</p>";
        echo "<p>Verrai reindirizzato alla dashboard tra 2 secondi...</p>";
        
        // Reindirizza dopo 2 secondi
        echo "<script>setTimeout(function(){ window.location = 'dashboard.php'; }, 2000);</script>";
        return true;
    } else {
        echo "<div style='color:red; font-weight:bold;'>Utente non trovato!</div>";
        return false;
    }
}

// Processa il login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    direct_login($username);
}

// Debug degli utenti disponibili
echo "<h2>Utenti disponibili:</h2>";
echo "<pre>";
echo "Numero totale utenti: " . count($demo_users) . "\n\n";

foreach ($demo_users as $user) {
    echo "ID: " . $user['id'] . ", Username: " . $user['username'];
    if (isset($user['display_name'])) {
        echo ", Nome: " . $user['display_name'];
    }
    echo "\n";
}
echo "</pre>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Semplificato (Corretto)</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .login-form { margin: 20px 0; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        input[type="text"] { padding: 8px; width: 300px; margin-right: 10px; }
        button { padding: 8px 16px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        .links { margin-top: 20px; }
        a { display: inline-block; margin-right: 15px; color: #0066cc; }
    </style>
</head>
<body>
    <h1>Login Semplificato (Corretto)</h1>
    
    <div class="login-form">
        <p>Inserisci solo il nome utente (la password è sempre "user123"):</p>
        <form method="post">
            <input type="text" name="username" placeholder="Username (es. anselmoacquah)" required>
            <button type="submit">Accedi</button>
        </form>
        <p><small>Puoi anche inserire solo nome o cognome: anselmo, davide, beatrice, ecc.</small></p>
    </div>
    
    <div class="links">
        <a href="dashboard_debug.php">Visualizza debug dashboard</a>
        <a href="index.php">Torna al login originale</a>
        <a href="test_login.php">Vai alla pagina test login</a>
    </div>
</body>
</html>