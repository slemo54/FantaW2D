<?php
// Carica le configurazioni originali
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Inizializza i dati demo
initialize_demo_data();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Funzione per il login diretto
function direct_login($username) {
    global $demo_users;
    
    // Normalizza username (tutto minuscolo)
    $username = strtolower($username);
    
    // Gestione speciale per nomi ambigui
    if ($username === 'andrea') {
        // Mostra una notifica e rimanda alla pagina di login
        $_SESSION['notification'] = [
            'message' => 'Ci sono più utenti con il nome Andrea. Usa il cognome (es. cariglia, darra, mattei) o il nome completo.',
            'type' => 'warning'
        ];
        return false;
    }
    
    if ($username === 'sara') {
        // Mostra una notifica e rimanda alla pagina di login
        $_SESSION['notification'] = [
            'message' => 'Ci sono più utenti con il nome Sara. Usa il cognome (es. lacagnina, zambon) o il nome completo.',
            'type' => 'warning'
        ];
        return false;
    }
    
    if ($username === 'elena') {
        // Mostra una notifica e rimanda alla pagina di login
        $_SESSION['notification'] = [
            'message' => 'Ci sono più utenti con il nome Elena. Usa il cognome (es. voloshina, zilotova) o il nome completo.',
            'type' => 'warning'
        ];
        return false;
    }
    
    // Mappa per nomi alternativi
    $name_map = [
        'anselmo' => 'anselmoacquah',
        'acquah' => 'anselmoacquah',
        'davide' => 'davidezanella',
        'zanella' => 'davidezanella',
        'beatrice' => 'beatricemotterle',
        'motterle' => 'beatricemotterle',
        'cariglia' => 'andreacariglia',
        'darra' => 'andreadarra',
        'mattei' => 'andreamattei',
        'federico' => 'federicozocca',
        'zocca' => 'federicozocca',
        'giorgia' => 'giorgiarangoni',
        'rangoni' => 'giorgiarangoni',
        'karla' => 'karlaravagnolo',
        'ravagnolo' => 'karlaravagnolo',
        'manuela' => 'manuelaclarizia',
        'clarizia' => 'manuelaclarizia',
        'marco' => 'marcogandini',
        'gandini' => 'marcogandini',
        'marina' => 'marinalovato',
        'lovato' => 'marinalovato',
        'michela' => 'michelaguerra',
        'guerra' => 'michelaguerra',
        'miriam' => 'miriamferrari',
        'ferrari' => 'miriamferrari',
        'roza' => 'rozazharmukhambetova',
        'richard' => 'richardhough',
        'hough' => 'richardhough',
        'lacagnina' => 'saralacagnina',
        'zambon' => 'sarazambon',
        'simone' => 'simonegallo',
        'gallo' => 'simonegallo',
        'valeria' => 'valeriabianchin',
        'bianchin' => 'valeriabianchin',
        'veronica' => 'veronicapimazzon',
        'pimazzon' => 'veronicapimazzon',
        'voloshina' => 'elenavoloshina',
        'zilotova' => 'elenazilotova',
        'admin' => 'admin'
    ];
    
    // Se il nome è nella mappa, usa la versione mappata
    if (isset($name_map[$username])) {
        $username = $name_map[$username];
    }
    
    // Login speciale per admin
    if ($username === 'admin') {
        $_SESSION['user_id'] = 1;
        return true;
    }
    
    // Cerca l'utente
    foreach ($demo_users as $user) {
        if (strtolower($user['username']) === $username) {
            // Login riuscito
            $_SESSION['user_id'] = $user['id'];
            return true;
        }
    }
    
    // Utente non trovato
    return false;
}

// Processa il login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    
    if (direct_login($username)) {
        // Login riuscito, imposta notifica e reindirizza
        $_SESSION['notification'] = [
            'message' => 'Login riuscito!',
            'type' => 'success'
        ];
        header('Location: dashboard.php');
        exit;
    } else {
        // Login fallito, imposta notifica di errore
        $_SESSION['notification'] = [
            'message' => 'Utente non trovato',
            'type' => 'error'
        ];
    }
}

// Include l'header se esiste
if (file_exists('includes/header.php')) {
    include 'includes/header.php';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fanta W2D - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #e74c3c;
            --accent-color: #f39c12;
            --text-color: #333;
            --light-bg: #f5f9fa;
            --card-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            max-width: 800px; 
            margin: 0 auto; 
            padding: 20px; 
            background-color: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .card {
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 30px;
            margin-bottom: 30px;
            border-top: 4px solid var(--primary-color);
        }
        
        h2 { 
            color: var(--primary-color); 
            margin-top: 0;
            font-weight: 600;
            font-size: 28px;
            margin-bottom: 20px;
        }
        
        h3 {
            color: var(--primary-color);
            margin-top: 0;
            font-weight: 500;
            font-size: 20px;
            margin-bottom: 15px;
        }
        
        .login-form { 
            margin: 25px 0; 
        }
        
        input[type="text"] { 
            padding: 14px; 
            width: 100%; 
            border: 1px solid #e1e1e1; 
            border-radius: 8px;
            box-sizing: border-box;
            margin-bottom: 20px;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        input[type="text"]:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }
        
        button { 
            padding: 14px 20px; 
            background-color: var(--primary-color); 
            color: white; 
            border: none; 
            border-radius: 8px;
            cursor: pointer; 
            font-size: 16px;
            width: 100%;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        button:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }
        
        .user-list {
            margin-top: 30px;
            font-size: 0.95em;
        }
        
        .info-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 25px;
            border-left: 4px solid var(--accent-color);
        }
        
        .notification-box {
            background-color: #e7f4fd;
            padding: 20px;
            border-radius: 8px;
            color: #0c5460;
            margin-top: 25px;
            border-left: 4px solid var(--primary-color);
        }
        
        .error {
            color: #721c24;
            background-color: #f8d7da;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid var(--secondary-color);
        }
        
        .success {
            color: #155724;
            background-color: #d4edda;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #28a745;
        }
        
        .icon-text {
            display: flex;
            align-items: center;
        }
        
        .icon-text i {
            margin-right: 10px;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <?php if (!isset($_SESSION['header_included'])): ?>
    <div class="card">
        <h2><i class="fas fa-trophy"></i> Fanta W2D - Login</h2>
        <p>Accedi per gestire il tuo saldo e le penalità/bonus del gruppo di lavoro</p>
        
        <?php if (isset($_SESSION['notification'])): ?>
            <div class="<?php echo $_SESSION['notification']['type']; ?>">
                <?php echo $_SESSION['notification']['message']; ?>
            </div>
            <?php unset($_SESSION['notification']); ?>
        <?php endif; ?>
        
        <div class="login-form">
            <form method="post">
                <input type="text" name="username" placeholder="Inserisci il tuo nome, cognome o username (es. anselmo, acquah, davidezanella)" required>
                <button type="submit"><i class="fas fa-sign-in-alt"></i> Accedi</button>
            </form>
            <p><small>Puoi inserire il nome utente completo o solo nome/cognome. Non serve password!</small></p>
            <p><small>Nota: per omonimie (Sara, Andrea, Elena), usa il cognome.</small></p>
            <p><small>Esempio: <code>anselmo</code>, <code>acquah</code> o <code>anselmoacquah</code> per Anselmo Acquah</small></p>
        </div>
        
        <div class="info-box">
            <h3><i class="fas fa-info-circle"></i> Come Funziona</h3>
            <div class="icon-text">
                <i class="fas fa-minus-circle"></i>
                <div>
                    <p><strong>Malus:</strong> Penalità per comportamenti fastidiosi o divertenti</p>
                    <ul>
                        <li>Malus #1: €0.50</li>
                        <li>Malus #2: €1.00</li>
                        <li>Extra Malus: €2.00</li>
                    </ul>
                </div>
            </div>
            
            <div class="icon-text">
                <i class="fas fa-plus-circle"></i>
                <div>
                    <p><strong>Bonus:</strong> Premi per azioni positive (+€0.50 o +€1.00)</p>
                </div>
            </div>
            
            <div class="icon-text">
                <i class="fas fa-magic"></i>
                <div>
                    <p><strong>Jolly:</strong> Ogni utente può cancellare un malus una volta al mese</p>
                </div>
            </div>
            
            <p>Il tuo saldo tiene traccia di tutti i malus (negativi) e bonus (positivi).</p>
        </div>
        
        <div class="notification-box">
            <h3><i class="fas fa-bell"></i> Notifiche</h3>
            <p>L'applicazione supporta notifiche email per:</p>
            <ul>
                <li><i class="fas fa-minus-circle"></i> Ricevere un malus</li>
                <li><i class="fas fa-plus-circle"></i> Ricevere un bonus</li>
                <li><i class="fas fa-magic"></i> Utilizzare un jolly</li>
                <li><i class="fas fa-sync"></i> Reset del jolly da parte dell'amministratore</li>
            </ul>
            <p>Assicurati di fornire il tuo indirizzo email nel tuo profilo per ricevere le notifiche.</p>
        </div>
        
        <div class="user-list">
            <h3><i class="fas fa-users"></i> Utenti</h3>
            <p>Puoi accedere in vari modi:</p>
            <ul>
                <li>Con il tuo <strong>nome</strong> (es. <code>anselmo</code>)</li>
                <li>Con il tuo <strong>cognome</strong> (es. <code>acquah</code>)</li>
                <li>Con il tuo <strong>username completo</strong> (es. <code>anselmoacquah</code>)</li>
            </ul>
            <p>Admin: <code>admin</code></p>
        </div>
    </div>
    <?php endif; ?>
    
    <?php
    // Include il footer se esiste
    if (file_exists('includes/footer.php')) {
        include 'includes/footer.php';
    }
    ?>
</body>
</html>