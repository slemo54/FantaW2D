<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'salvadanaio_w2d';

// Application settings
$app_name = 'Fanta W2D';
$app_version = '1.0';

// Malus and Bonus configurations
$malus_types = [
    1 => [
        'name' => 'Malus #1',
        'amount' => 0.50
    ],
    2 => [
        'name' => 'Malus #2',
        'amount' => 1.00
    ],
    3 => [
        'name' => 'Extra Malus',
        'amount' => 2.00
    ]
];

// Bonus configurations
$bonus_amounts = [
    0.50,
    1.00
];

// Utenti specifici e regole predefinite per malus
$user_specific_malus = [
    'Andrea Cariglia' => [
        'malus1' => 'If Paglialunga talks to him',
        'malus2' => '',
        'extra' => ''
    ],
    'Andrea Darra' => [
        'malus1' => 'If he takes random pics of any colleagues',
        'malus2' => 'If he starts talking about random things (philosophical, historical, sociological, political)',
        'extra' => 'If in the middle of the conversation, he leaves but keeps the conversation going from another room/location'
    ],
    'Andrea Mattei' => [
        'malus1' => 'If any woman or man flirts with him',
        'malus2' => '',
        'extra' => ''
    ],
    'Anselmo Acquah' => [
        'malus1' => 'If he eats anything from the \'reparto surgelati\'',
        'malus2' => 'If he loses his phone or glasses',
        'extra' => ''
    ],
    'Beatrice Motterle' => [
        'malus1' => 'If she sings',
        'malus2' => 'If Stevie says \'trust me\' when planning for a podcast series',
        'extra' => ''
    ],
    'Cynthia Chaplin' => [
        'malus1' => 'If he/she nods while saying "mmh" or "mhh."',
        'malus2' => '',
        'extra' => ''
    ],
    'Davide Zanella' => [
        'malus1' => 'If he sleeps in the afternoon',
        'malus2' => '',
        'extra' => ''
    ],
    'Elena Voloshina' => [
        'malus1' => '',
        'malus2' => '',
        'extra' => ''
    ],
    'Elena Zilotova' => [
        'malus1' => '',
        'malus2' => '',
        'extra' => ''
    ],
    'Federico Zocca' => [
        'malus1' => 'If he says "dimmi"',
        'malus2' => '',
        'extra' => 'If he has to take any institutional photos (Zoppas, Ministro, VF)'
    ],
    'Giorgia Rangoni' => [
        'malus1' => 'If she says "Zio Can" or "Mona" or "Fra"',
        'malus2' => '',
        'extra' => ''
    ],
    'Karla Ravagnolo' => [
        'malus1' => 'If she says "Cute" or "Fuah" or "Daaaamn" or "Girrrl"',
        'malus2' => '',
        'extra' => ''
    ],
    'Manuela Clarizia' => [
        'malus1' => 'If she calls any andrea 3 times in a row screaming',
        'malus2' => 'If she has to modify a transfer',
        'extra' => 'If somebody calls her Clarizia'
    ],
    'Marco Gandini' => [
        'malus1' => '',
        'malus2' => '',
        'extra' => ''
    ],
    'Marina Lovato' => [
        'malus1' => 'If Stevie says she is "la memoria storica/pilastro"',
        'malus2' => '',
        'extra' => ''
    ],
    'Michela Guerra' => [
        'malus1' => '',
        'malus2' => '',
        'extra' => ''
    ],
    'Miriam Ferrari' => [
        'malus1' => 'If she mentions any words in venetian dialect',
        'malus2' => '',
        'extra' => ''
    ],
    'Roza Zharmukhambetova' => [
        'malus1' => 'If Stevie says \'trust me\' when planning for a podcast series',
        'malus2' => '',
        'extra' => ''
    ],
    'Richard Hough' => [
        'malus1' => 'If he changes his hat',
        'malus2' => '',
        'extra' => ''
    ],
    'Sara La Cagnina' => [
        'malus1' => '',
        'malus2' => '',
        'extra' => ''
    ],
    'Sara Zambon' => [
        'malus1' => '',
        'malus2' => '',
        'extra' => ''
    ],
    'Simone Gallo' => [
        'malus1' => 'If he touches his beard',
        'malus2' => 'If he "air plays" the drums',
        'extra' => ''
    ],
    'Valeria Bianchin' => [
        'malus1' => '',
        'malus2' => '',
        'extra' => ''
    ],
    'Veronica Pimazzon' => [
        'malus1' => 'If she says "mammacara"',
        'malus2' => '',
        'extra' => ''
    ]
];

// Types of positive behaviors/actions that deserve bonus
$positive_actions = [
    'Helps a colleague with a problem',
    'Brings food/snacks to share with everyone',
    'Completes a task before deadline',
    'Helps with office cleaning/organization',
    'Takes initiative in a project',
    'Proposes a good idea for improvement',
    'Solves a critical issue',
    'Helps a new employee',
    'Stays late to finish important work',
    'Receives positive feedback from clients'
];

// For demo/development purposes (no database)
// In production, this would be stored in the database
$demo_users = [
    [
        'id' => 1,
        'username' => 'admin',
        'password' => 'admin123', // In production, use password_hash()
        'role' => 'admin',
        'balance' => 0.00,
        'jolly_used' => false
    ]
];

// List of special users with additional privileges
$special_users = [
    'karlaravagnolo' => true,
    'admin' => true
];

// Add all users from the user_specific_malus array
$user_id = 2;
foreach ($user_specific_malus as $name => $malus) {
    // Create a username from the name (lowercase, no spaces)
    $username = strtolower(str_replace(' ', '', $name));
    
    // Debug
    error_log("Creando utente demo: " . $username . " dal nome: " . $name);
    
    // Add user
    $demo_users[] = [
        'id' => $user_id,
        'username' => $username,
        'password' => 'user123', // Default password
        'role' => 'user',
        'balance' => 0.00,
        'jolly_used' => false,
        'display_name' => $name // Add display name for showing in the UI
    ];
    
    $user_id++;
}

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set session save path to avoid permission issues
    session_save_path(sys_get_temp_dir());
    
    // Enable error reporting in development
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    session_start();
}