<?php
require_once 'config_db.php';

/**
 * Classe per gestire l'invio di email tramite SMTP
 */
class EmailNotifications {
    private $host;
    private $port;
    private $username;
    private $password;
    private $from_email;
    private $from_name;
    
    /**
     * Costruttore
     */
    public function __construct() {
        global $smtp_config;
        
        $this->host = $smtp_config['host'];
        $this->port = $smtp_config['port'];
        $this->username = $smtp_config['username'];
        $this->password = $smtp_config['password'];
        $this->from_email = $smtp_config['from_email'];
        $this->from_name = $smtp_config['from_name'];
    }
    
    /**
     * Invia una email
     * 
     * @param string $to_email Email del destinatario
     * @param string $to_name Nome del destinatario
     * @param string $subject Oggetto della mail
     * @param string $body Corpo della mail
     * @param bool $is_html Se il corpo della mail è in formato HTML
     * @return bool True se l'invio è riuscito, false altrimenti
     */
    public function sendEmail($to_email, $to_name, $subject, $body, $is_html = true) {
        // Verifica che siano stati specificati i parametri obbligatori
        if (empty($to_email) || empty($subject) || empty($body)) {
            error_log("Email notification: missing required parameters");
            return false;
        }
        
        // Headers
        $headers = [
            'From' => $this->from_name . ' <' . $this->from_email . '>',
            'To' => $to_name . ' <' . $to_email . '>',
            'Subject' => $subject,
            'Date' => date('r'),
            'Message-ID' => '<' . time() . '-' . md5($to_email . $subject) . '@' . $_SERVER['SERVER_NAME'] . '>'
        ];
        
        if ($is_html) {
            $headers['MIME-Version'] = '1.0';
            $headers['Content-Type'] = 'text/html; charset=UTF-8';
        } else {
            $headers['Content-Type'] = 'text/plain; charset=UTF-8';
        }
        
        // Formatta gli headers per mail()
        $header_str = '';
        foreach ($headers as $key => $value) {
            $header_str .= $key . ': ' . $value . "\r\n";
        }
        
        // Parametri aggiuntivi per mail()
        $additional_params = '-f' . $this->from_email;
        
        // Invia l'email
        $result = mail($to_email, $subject, $body, $header_str, $additional_params);
        
        if (!$result) {
            error_log("Errore nell'invio dell'email a $to_email");
            return false;
        }
        
        return true;
    }
    
    /**
     * Invia una notifica quando viene aggiunto un malus a un utente
     * 
     * @param array $user Dati dell'utente
     * @param array $transaction Dati della transazione
     * @param array $creator Dati dell'utente che ha creato la transazione
     * @return bool True se l'invio è riuscito, false altrimenti
     */
    public function sendMalusNotification($user, $transaction, $creator) {
        // Verifica che l'utente abbia un'email
        if (empty($user['email'])) {
            return false;
        }
        
        $subject = "Salvadanaio W2D - Hai ricevuto un malus";
        
        $amount = number_format($transaction['amount'], 2, '.', ',');
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #FF5722; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .footer { margin-top: 20px; font-size: 12px; color: #777; text-align: center; }
                .malus { color: #FF5722; font-weight: bold; }
                .amount { font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Salvadanaio W2D</h1>
                </div>
                <div class='content'>
                    <p>Ciao {$user['display_name']},</p>
                    <p>Hai ricevuto un <span class='malus'>{$transaction['sub_type']}</span> di <span class='amount'>€{$amount}</span>.</p>
                    <p><strong>Motivo:</strong> {$transaction['description']}</p>
                    <p><strong>Aggiunto da:</strong> {$creator['display_name']}</p>
                    <p><strong>Data:</strong> " . date('d/m/Y H:i', strtotime($transaction['timestamp'])) . "</p>
                    <p>Il tuo saldo attuale è <span class='amount'>€" . number_format($user['balance'], 2, '.', ',') . "</span></p>
                    <p>Accedi all'applicazione per utilizzare il tuo Jolly e cancellare questo malus.</p>
                </div>
                <div class='footer'>
                    <p>Questa è una notifica automatica, si prega di non rispondere a questa email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->sendEmail($user['email'], $user['display_name'], $subject, $body);
    }
    
    /**
     * Invia una notifica quando viene aggiunto un bonus a un utente
     * 
     * @param array $user Dati dell'utente
     * @param array $transaction Dati della transazione
     * @param array $creator Dati dell'utente che ha creato la transazione
     * @return bool True se l'invio è riuscito, false altrimenti
     */
    public function sendBonusNotification($user, $transaction, $creator) {
        // Verifica che l'utente abbia un'email
        if (empty($user['email'])) {
            return false;
        }
        
        $subject = "Salvadanaio W2D - Hai ricevuto un bonus";
        
        $amount = number_format($transaction['amount'], 2, '.', ',');
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4CAF50; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .footer { margin-top: 20px; font-size: 12px; color: #777; text-align: center; }
                .bonus { color: #4CAF50; font-weight: bold; }
                .amount { font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Salvadanaio W2D</h1>
                </div>
                <div class='content'>
                    <p>Ciao {$user['display_name']},</p>
                    <p>Hai ricevuto un <span class='bonus'>bonus</span> di <span class='amount'>€{$amount}</span>.</p>
                    <p><strong>Motivo:</strong> {$transaction['description']}</p>
                    <p><strong>Aggiunto da:</strong> {$creator['display_name']}</p>
                    <p><strong>Data:</strong> " . date('d/m/Y H:i', strtotime($transaction['timestamp'])) . "</p>
                    <p>Il tuo saldo attuale è <span class='amount'>€" . number_format($user['balance'], 2, '.', ',') . "</span></p>
                </div>
                <div class='footer'>
                    <p>Questa è una notifica automatica, si prega di non rispondere a questa email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->sendEmail($user['email'], $user['display_name'], $subject, $body);
    }
    
    /**
     * Invia una notifica quando un utente usa il jolly
     * 
     * @param array $user Dati dell'utente
     * @param array $transaction Dati della transazione
     * @return bool True se l'invio è riuscito, false altrimenti
     */
    public function sendJollyNotification($user, $transaction) {
        // Verifica che l'utente abbia un'email
        if (empty($user['email'])) {
            return false;
        }
        
        $subject = "Salvadanaio W2D - Hai usato il tuo Jolly";
        
        $amount = number_format($transaction['amount'], 2, '.', ',');
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2196F3; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .footer { margin-top: 20px; font-size: 12px; color: #777; text-align: center; }
                .jolly { color: #2196F3; font-weight: bold; }
                .amount { font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Salvadanaio W2D</h1>
                </div>
                <div class='content'>
                    <p>Ciao {$user['display_name']},</p>
                    <p>Hai utilizzato il tuo <span class='jolly'>Jolly</span> per cancellare un malus di <span class='amount'>€{$amount}</span>.</p>
                    <p><strong>Malus cancellato:</strong> {$transaction['description']}</p>
                    <p><strong>Data:</strong> " . date('d/m/Y H:i') . "</p>
                    <p>Il tuo saldo attuale è <span class='amount'>€" . number_format($user['balance'], 2, '.', ',') . "</span></p>
                    <p>Ricorda che puoi utilizzare il Jolly solo una volta al mese.</p>
                </div>
                <div class='footer'>
                    <p>Questa è una notifica automatica, si prega di non rispondere a questa email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->sendEmail($user['email'], $user['display_name'], $subject, $body);
    }
    
    /**
     * Invia una notifica quando viene resettato il jolly di un utente
     * 
     * @param array $user Dati dell'utente
     * @return bool True se l'invio è riuscito, false altrimenti
     */
    public function sendJollyResetNotification($user) {
        // Verifica che l'utente abbia un'email
        if (empty($user['email'])) {
            return false;
        }
        
        $subject = "Salvadanaio W2D - Il tuo Jolly è stato resettato";
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2196F3; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .footer { margin-top: 20px; font-size: 12px; color: #777; text-align: center; }
                .jolly { color: #2196F3; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Salvadanaio W2D</h1>
                </div>
                <div class='content'>
                    <p>Ciao {$user['display_name']},</p>
                    <p>Il tuo <span class='jolly'>Jolly</span> è stato resettato dall'amministratore.</p>
                    <p>Ora puoi utilizzare nuovamente il tuo Jolly per cancellare un malus.</p>
                    <p><strong>Data:</strong> " . date('d/m/Y H:i') . "</p>
                </div>
                <div class='footer'>
                    <p>Questa è una notifica automatica, si prega di non rispondere a questa email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->sendEmail($user['email'], $user['display_name'], $subject, $body);
    }
}