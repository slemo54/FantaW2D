<?php
// Database configuration
// Credenziali per il tuo hosting (come fornite nelle immagini)
$db_host = 'localhost';

// SiteGround spesso richiede un prefisso per lo username
// Proviamo entrambe le varianti
$db_user = 'ul7jql5talqtq'; // Username senza prefisso
//$db_user = 'uanselmo_ul7jql5talqtq'; // Username con prefisso (decommentare se necessario)

$db_pass = '2k@#%}2(+2?j'; // Password dal tuo hosting

// SiteGround potrebbe richiedere un prefisso per il nome database
$db_name = 'dbymzupboptdzw'; // Nome database senza prefisso
//$db_name = 'uanselmo_dbymzupboptdzw'; // Nome database con prefisso (decommentare se necessario)

// Configurazione SMTP per email
$smtp_config = [
    'host' => 'mail.cosetek.it',
    'port' => 465,
    'username' => 'info@cosetek.it',
    'password' => '@3#3bp%4:5e4',
    'from_email' => 'info@cosetek.it',
    'from_name' => 'Fanta W2D'
];

// Connessione al database
function db_connect() {
    global $db_host, $db_user, $db_pass, $db_name;
    
    // Verifica se ci sono dati di accesso validi
    if (empty($db_user) || empty($db_host)) {
        return false;
    }
    
    try {
        // Log dei parametri di connessione
        error_log("Tentativo di connessione al database con i seguenti parametri:");
        error_log("Host: " . $db_host);
        error_log("User: " . $db_user);
        error_log("Database: " . $db_name);
        
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        
        // Verifica connessione
        if ($conn->connect_error) {
            error_log("Errore di connessione al database: " . $conn->connect_error);
            return false;
        }
        
        error_log("Connessione al database riuscita!");
        
        // Imposta charset a utf8
        $conn->set_charset("utf8");
        
        return $conn;
    } catch (Exception $e) {
        error_log("Eccezione nella connessione al database: " . $e->getMessage());
        return false;
    }
}

// Funzione per eseguire query
function db_query($sql, $params = []) {
    try {
        $conn = db_connect();
        
        if (!$conn) {
            // Se non riusciamo a connetterci al database, avvisiamo e usiamo la memoria di sessione
            error_log("Impossibile connettersi al database, uso la modalità sessione");
            return false;
        }
        
        // Cattura gli errori relativi a tabelle mancanti
        if (strpos(strtoupper($sql), 'SELECT') === 0 || 
            strpos(strtoupper($sql), 'UPDATE') === 0 || 
            strpos(strtoupper($sql), 'DELETE') === 0) {
            
            // Estrai il nome della tabella dalla query (approssimativo)
            preg_match('/\s+FROM\s+[`]?(\w+)[`]?/i', $sql, $matches);
            $table_name = isset($matches[1]) ? $matches[1] : '';
            
            if (!empty($table_name)) {
                // Verifica se la tabella esiste
                $check_table = $conn->query("SHOW TABLES LIKE '$table_name'");
                if ($check_table && $check_table->num_rows == 0) {
                    throw new Exception("Table '$table_name' doesn't exist");
                }
            }
        }
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Errore nella preparazione della query: " . $conn->error);
            $conn->close();
            return false;
        }
        
        // Binding dei parametri
        if (!empty($params)) {
            $types = '';
            $bindParams = [];
            
            // Crea la stringa dei tipi
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
                $bindParams[] = $param;
            }
            
            // Crea un array con tutti i parametri da passare a bind_param
            $bindValues = array_merge([$types], $bindParams);
            
            // Converti i parametri in riferimenti (richiesto da bind_param)
            $refs = [];
            foreach ($bindValues as $key => $value) {
                $refs[$key] = &$bindValues[$key];
            }
            
            // Binding dei parametri
            call_user_func_array([$stmt, 'bind_param'], $refs);
        }
        
        // Esecuzione della query
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("Errore nell'esecuzione della query: " . $stmt->error . " - SQL: " . $sql);
            $stmt->close();
            $conn->close();
            return false;
        }
        
        // Se è una query SELECT, ottieni i risultati
        if (strpos(strtoupper($sql), 'SELECT') === 0) {
            $resultData = $stmt->get_result();
            $data = [];
            
            while ($row = $resultData->fetch_assoc()) {
                $data[] = $row;
            }
            
            $stmt->close();
            $conn->close();
            
            return $data;
        }
        
        // Se è una query INSERT, restituisci l'id inserito
        if (strpos(strtoupper($sql), 'INSERT') === 0) {
            $insertId = $conn->insert_id;
            $stmt->close();
            $conn->close();
            
            return $insertId;
        }
        
        // Per altre query (UPDATE, DELETE) restituisci numero di righe modificate
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        $conn->close();
        
        return $affectedRows;
    } catch (Exception $e) {
        error_log("Eccezione nell'esecuzione della query: " . $e->getMessage() . " - SQL: " . $sql);
        // Verifica se l'errore è relativo a tabella mancante
        if (strpos($e->getMessage(), "Table") !== false && strpos($e->getMessage(), "doesn't exist") !== false) {
            $_SESSION['setup_mode'] = true;
        }
        return false;
    }
}

// Funzione per ottenere una singola riga
function db_get_row($sql, $params = []) {
    $result = db_query($sql, $params);
    
    if ($result && is_array($result) && count($result) > 0) {
        return $result[0];
    }
    
    return null;
}

// Flag per forzare la modalità sessione
$force_session_mode = false;

// Funzione per verificare se stiamo usando il database
function is_using_database() {
    global $force_session_mode;
    
    // Se è stata forzata la modalità sessione, non tentare nemmeno la connessione
    if ($force_session_mode) {
        error_log("Modalità sessione forzata attivata");
        return false;
    }
    
    // Contatore dei tentativi di connessione
    static $connection_attempts = 0;
    
    // Se ci sono stati troppi tentativi falliti, passa alla modalità sessione
    if ($connection_attempts >= 3) {
        error_log("Troppi tentativi falliti di connessione al database, attivo modalità sessione");
        $force_session_mode = true;
        return false;
    }
    
    // Prova a connettersi al database
    $conn = db_connect();
    
    if ($conn) {
        $conn->close();
        return true;
    }
    
    // Incrementa il contatore dei tentativi falliti
    $connection_attempts++;
    
    // Se non riesce a connettersi, usa la memorizzazione in sessione
    return false;
}