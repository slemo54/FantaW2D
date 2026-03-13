# Fanta W2D

Un'applicazione per tenere traccia di malus e bonus nel gruppo di lavoro.

## Panoramica

Questa applicazione consente di:

- Assegnare penalità (Malus) per comportamenti fastidiosi o divertenti
- Assegnare bonus per azioni positive
- Tenere traccia del saldo di ciascun utente
- Utilizzare un "Jolly" una volta al mese per cancellare una penalità
- Ricevere notifiche email per transazioni importanti

## Installazione

1. Carica tutti i file sul tuo server PHP
2. Crea il database MySQL (o lascia che l'applicazione funzioni con la modalità sessione)
3. Configura i parametri del database in `includes/config_db.php`

### Configurazione del Database

1. Accedi come amministratore con le credenziali predefinite:
   - Username: `admin`
   - Password: `admin123`
2. Vai alla pagina di amministrazione (`admin.php`)
3. Fai clic su "Setup Database" per creare automaticamente lo schema del database

## Credenziali Database

Modifica il file `includes/config_db.php` con le tue credenziali del database:

```php
$db_host = 'localhost';
$db_user = 'uanselmo_w2d';    // Sostituisci con il tuo username database
$db_pass = 'W2dDatabase!';   // Sostituisci con la tua password database
$db_name = 'dbw2d_salvadanaio';
```

## Configurazione Email

Le notifiche email sono configurate nel file `includes/config_db.php`:

```php
$smtp_config = [
    'host' => 'mail.cosetek.it',
    'port' => 465,
    'username' => 'info@cosetek.it',
    'password' => '@3#3bp%4:5e4',
    'from_email' => 'info@cosetek.it',
    'from_name' => 'Fanta W2D'
];
```

## Utenti Predefiniti

L'applicazione include i seguenti utenti predefiniti:

- **Admin**: 
  - Username: `admin`
  - Password: `admin123`

- **Utenti standard**: 
  - Username: `nome+cognome` (tutto minuscolo, senza spazi)
  - Password: `user123`
  - Esempio: `andreacariglia` / `user123`

## Funzionalità

### Tipi di Malus
- **Malus #1**: €0.50
- **Malus #2**: €1.00
- **Extra Malus**: €2.00

### Bonus
- Importi: €0.50 o €1.00
- Assegnati per comportamenti positivi

### Jolly
- Ogni utente può utilizzare un Jolly una volta al mese
- Il Jolly permette di cancellare una penalità
- Solo gli amministratori possono resettare i Jolly

### Notifiche Email
L'applicazione invia notifiche email per:
- Ricevere un malus
- Ricevere un bonus
- Utilizzare un jolly
- Reset del jolly da parte dell'amministratore

## Sviluppo

### Struttura dei File

```
/
├── index.php (pagina di login)
├── dashboard.php (dashboard utente)
├── admin.php (pannello amministrativo)
├── transactions.php (gestione transazioni)
├── profile.php (profilo utente)
├── leaderboard.php (classifica)
├── includes/
│   ├── config.php (configurazione applicazione)
│   ├── config_db.php (configurazione database)
│   ├── functions.php (funzioni condivise)
│   ├── auth.php (gestione autenticazione)
│   ├── email_notifications.php (notifiche email)
│   └── header.php, footer.php (elementi comuni)
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
└── api/
    ├── add_transaction.php
    ├── use_jolly.php
    ├── reset_jolly.php
    └── add_user.php
```

### Modalità Database vs Sessione

L'applicazione può funzionare in due modalità:
1. **Modalità Database**: Utilizza MySQL per la persistenza dei dati
2. **Modalità Sessione**: Memorizza i dati nella sessione PHP (utile per testing)

L'applicazione tenta automaticamente di connettersi al database e, se non ci riesce, passa alla modalità sessione.

## Licenza

Salvadanaio W2D è un'applicazione privata per uso interno.