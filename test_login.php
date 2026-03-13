<?php
// Questo file verifica il sistema di login

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Debug informazioni
echo "<h1>Test Sistema Login</h1>";

// Controlla se is_using_database() è true o false
echo "<h2>Modalità Database o Sessione</h2>";
echo "is_using_database(): " . (is_using_database() ? 'true (database)' : 'false (sessione)') . "<br>";

// Visualizza l'array demo_users
echo "<h2>Utenti Demo Disponibili</h2>";
echo "Numero di utenti: " . count($demo_users) . "<br>";

echo "<table border='1'>";
echo "<tr><th>ID</th><th>Username</th><th>Password</th><th>Display Name</th><th>Role</th></tr>";

foreach ($demo_users as $user) {
    echo "<tr>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td>" . $user['username'] . "</td>";
    echo "<td>" . $user['password'] . "</td>";
    echo "<td>" . (isset($user['display_name']) ? $user['display_name'] : '-') . "</td>";
    echo "<td>" . $user['role'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Testa specificamente il login per anselmoacquah
echo "<h2>Test Login per anselmoacquah</h2>";

$test_username = 'anselmoacquah';
$test_password = 'user123';

// Trova l'utente nell'array demo_users per debug
$found = false;
foreach ($demo_users as $user) {
    if ($user['username'] === $test_username) {
        echo "Utente trovato nell'array demo_users.<br>";
        echo "Username: " . $user['username'] . "<br>";
        echo "Password: " . $user['password'] . "<br>";
        $found = true;
        break;
    }
}

if (!$found) {
    echo "ERRORE: Utente anselmoacquah NON trovato nell'array demo_users!<br>";
}

// Prova il login
echo "<h3>Test login($test_username, $test_password)</h3>";
$result = login($test_username, $test_password);
echo "Risultato: " . ($result ? 'SUCCESS' : 'FAILURE') . "<br>";

// Verifica la sessione
echo "<h3>Contenuto della Sessione</h3>";
echo "SESSION['user_id']: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'non impostato') . "<br>";
echo "SESSION['users'] count: " . (isset($_SESSION['users']) ? count($_SESSION['users']) : 'non impostato') . "<br>";

// Form per testare altri utenti
echo "<h2>Prova un altro login</h2>";
echo '<form method="post">';
echo 'Username: <input type="text" name="username"><br>';
echo 'Password: <input type="password" name="password"><br>';
echo '<input type="submit" value="Test Login">';
echo '</form>';

// Processa il form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    echo "<h3>Test login($username, $password)</h3>";
    $result = login($username, $password);
    echo "Risultato: " . ($result ? 'SUCCESS' : 'FAILURE') . "<br>";
    
    // Verifica la sessione
    echo "<h3>Contenuto della Sessione dopo login</h3>";
    echo "SESSION['user_id']: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'non impostato') . "<br>";
}

echo "<hr>";
echo "<a href='index.php'>Torna alla pagina di login</a>";