<?php
// Inizializza la sessione
session_start();

// Funzione per la creazione di un utente di base
function create_user($id, $username, $display_name, $role = 'user') {
    return [
        'id' => $id,
        'username' => strtolower($username),
        'password' => 'user123',
        'role' => $role,
        'display_name' => $display_name,
        'balance' => 0.00,
        'jolly_used' => false
    ];
}

// Crea utenti fissi
$users = [
    create_user(1, 'admin', 'Administrator', 'admin'),
    create_user(2, 'andreacariglia', 'Andrea Cariglia'),
    create_user(3, 'andreadarra', 'Andrea Darra'),
    create_user(4, 'anselmoacquah', 'Anselmo Acquah'),
    create_user(5, 'beatricemotterle', 'Beatrice Motterle'),
    create_user(6, 'cynthiachaplin', 'Cynthia Chaplin'),
    create_user(7, 'davidezanella', 'Davide Zanella')
];

// Processa il login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    echo "<h3>Tentativo di login con username '" . htmlspecialchars($username) . "' e password '" . htmlspecialchars($password) . "'</h3>";
    
    $success = false;
    foreach ($users as $user) {
        if ($user['username'] === $username && $user['password'] === $password) {
            // Login riuscito
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['display_name'];
            $_SESSION['user_role'] = $user['role'];
            $success = true;
            
            // Salva gli utenti nella sessione per altre pagine
            $_SESSION['users'] = $users;
            
            // Debug
            echo "<pre>Utenti nella sessione:\n";
            foreach ($_SESSION['users'] as $u) {
                echo "ID: " . $u['id'] . ", Username: " . $u['username'] . ", Display: " . $u['display_name'] . "\n";
            }
            echo "</pre>";
            
            echo "<div style='color: green; font-weight: bold;'>Login riuscito! Benvenuto " . htmlspecialchars($user['display_name']) . "</div>";
            echo "<p>ID utente: " . $user['id'] . "</p>";
            echo "<p>Redirect in corso...</p>";
            echo "<script>setTimeout(function(){ window.location = 'dashboard.php'; }, 2000);</script>";
            break;
        }
    }
    
    if (!$success) {
        echo "<div style='color: red; font-weight: bold;'>Login fallito. Username o password non validi.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Semplificato</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .login-form { max-width: 400px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background-color: #4CAF50; color: white; padding: 10px 15px; border: none; cursor: pointer; }
        .user-list { margin-top: 30px; }
        .user-list table { width: 100%; border-collapse: collapse; }
        .user-list th, .user-list td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .user-list th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Login Semplificato</h1>
    
    <div class="login-form">
        <h2>Accedi</h2>
        <form method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Accedi</button>
        </form>
    </div>
    
    <div class="user-list">
        <h2>Utenti disponibili</h2>
        <p>Tutti gli utenti hanno password: <strong>user123</strong></p>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Nome visualizzato</th>
                <th>Ruolo</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['display_name']); ?></td>
                <td><?php echo $user['role']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <p><a href="index.php">Torna alla pagina di login originale</a></p>
</body>
</html>