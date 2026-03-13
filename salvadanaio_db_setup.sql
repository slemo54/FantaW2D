-- Script SQL completo per il Salvadanaio W2D
-- Importare questo script direttamente in phpMyAdmin o altro strumento di gestione database
-- NON è necessario utilizzare il comando "Setup Database" dall'interfaccia

-- Creazione delle tabelle
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `jolly_used` tinyint(1) NOT NULL DEFAULT 0,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `user_malus_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `malus_type` enum('malus1','malus2','extra') NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_malus_rules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('malus','bonus') NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text NOT NULL,
  `sub_type` varchar(50) DEFAULT NULL,
  `cancelled` tinyint(1) NOT NULL DEFAULT 0,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `bonus_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserimento dell'utente admin di default
-- Password: admin123 (hash bcrypt)
INSERT INTO `users` (`username`, `password`, `display_name`, `role`, `balance`, `jolly_used`)
VALUES ('admin', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Administrator', 'admin', 0.00, 0);

-- Inserimento di alcuni bonus predefiniti
INSERT INTO `bonus_rules` (`description`) VALUES
('Helps a colleague with a problem'),
('Brings food/snacks to share with everyone'),
('Completes a task before deadline'),
('Helps with office cleaning/organization'),
('Takes initiative in a project'),
('Proposes a good idea for improvement'),
('Solves a critical issue'),
('Helps a new employee'),
('Stays late to finish important work'),
('Receives positive feedback from clients');

-- Inserimento degli utenti predefiniti
-- Password: user123 (hash bcrypt)
INSERT INTO `users` (`username`, `password`, `display_name`, `role`, `balance`, `jolly_used`)
VALUES 
('andreacariglia', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Andrea Cariglia', 'user', 0.00, 0),
('andreadarra', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Andrea Darra', 'user', 0.00, 0),
('andreamattei', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Andrea Mattei', 'user', 0.00, 0),
('anselmoacquah', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Anselmo Acquah', 'user', 0.00, 0),
('beatricemotterle', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Beatrice Motterle', 'user', 0.00, 0),
('cynthiachaplin', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Cynthia Chaplin', 'user', 0.00, 0),
('davidezanella', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Davide Zanella', 'user', 0.00, 0),
('elenavoloshina', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Elena Voloshina', 'user', 0.00, 0),
('elenazilotova', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Elena Zilotova', 'user', 0.00, 0),
('federicozocca', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Federico Zocca', 'user', 0.00, 0),
('giorgiarangoni', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Giorgia Rangoni', 'user', 0.00, 0),
('karlaravagnolo', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Karla Ravagnolo', 'user', 0.00, 0),
('manuelaclarizia', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Manuela Clarizia', 'user', 0.00, 0),
('marcogandini', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Marco Gandini', 'user', 0.00, 0),
('marinalovato', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Marina Lovato', 'user', 0.00, 0),
('michelaguerra', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Michela Guerra', 'user', 0.00, 0),
('miriamferrari', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Miriam Ferrari', 'user', 0.00, 0),
('rozazharmukhambetova', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Roza Zharmukhambetova', 'user', 0.00, 0),
('richardhough', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Richard Hough', 'user', 0.00, 0),
('saralacagnina', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Sara La Cagnina', 'user', 0.00, 0),
('sarazambon', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Sara Zambon', 'user', 0.00, 0),
('simonegallo', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Simone Gallo', 'user', 0.00, 0),
('valeriabianchin', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Valeria Bianchin', 'user', 0.00, 0),
('veronicapimazzon', '$2y$10$4QD0PXIz1cvXUqJ1yKq6ZeKZK0qM0IFEVTh9Wg4UL6v4m6ACnL2Zi', 'Veronica Pimazzon', 'user', 0.00, 0);

-- Inserimento delle regole malus predefinite
INSERT INTO `user_malus_rules` (`user_id`, `malus_type`, `description`)
VALUES 
((SELECT id FROM users WHERE display_name = 'Andrea Cariglia'), 'malus1', 'If Paglialunga talks to him'),
((SELECT id FROM users WHERE display_name = 'Andrea Darra'), 'malus1', 'If he takes random pics of any colleagues'),
((SELECT id FROM users WHERE display_name = 'Andrea Darra'), 'malus2', 'If he starts talking about random things (philosophical, historical, sociological, political)'),
((SELECT id FROM users WHERE display_name = 'Andrea Darra'), 'extra', 'If in the middle of the conversation, he leaves but keeps the conversation going from another room/location'),
((SELECT id FROM users WHERE display_name = 'Andrea Mattei'), 'malus1', 'If any woman or man flirts with him'),
((SELECT id FROM users WHERE display_name = 'Anselmo Acquah'), 'malus1', 'If he eats anything from the \'reparto surgelati\''),
((SELECT id FROM users WHERE display_name = 'Anselmo Acquah'), 'malus2', 'If he loses his phone or glasses'),
((SELECT id FROM users WHERE display_name = 'Beatrice Motterle'), 'malus1', 'If she sings'),
((SELECT id FROM users WHERE display_name = 'Beatrice Motterle'), 'malus2', 'If Stevie says \'trust me\' when planning for a podcast series'),
((SELECT id FROM users WHERE display_name = 'Cynthia Chaplin'), 'malus1', 'If he/she nods while saying "mmh" or "mhh."'),
((SELECT id FROM users WHERE display_name = 'Davide Zanella'), 'malus1', 'If he sleeps in the afternoon'),
((SELECT id FROM users WHERE display_name = 'Federico Zocca'), 'malus1', 'If he says "dimmi"'),
((SELECT id FROM users WHERE display_name = 'Federico Zocca'), 'extra', 'If he has to take any institutional photos (Zoppas, Ministro, VF)'),
((SELECT id FROM users WHERE display_name = 'Giorgia Rangoni'), 'malus1', 'If she says "Zio Can" or "Mona" or "Fra"'),
((SELECT id FROM users WHERE display_name = 'Karla Ravagnolo'), 'malus1', 'If she says "Cute" or "Fuah" or "Daaaamn" or "Girrrl"'),
((SELECT id FROM users WHERE display_name = 'Manuela Clarizia'), 'malus1', 'If she calls any andrea 3 times in a row screaming'),
((SELECT id FROM users WHERE display_name = 'Manuela Clarizia'), 'malus2', 'If she has to modify a transfer'),
((SELECT id FROM users WHERE display_name = 'Manuela Clarizia'), 'extra', 'If somebody calls her Clarizia'),
((SELECT id FROM users WHERE display_name = 'Marina Lovato'), 'malus1', 'If Stevie says she is "la memoria storica/pilastro"'),
((SELECT id FROM users WHERE display_name = 'Miriam Ferrari'), 'malus1', 'If she mentions any words in venetian dialect'),
((SELECT id FROM users WHERE display_name = 'Roza Zharmukhambetova'), 'malus1', 'If Stevie says \'trust me\' when planning for a podcast series'),
((SELECT id FROM users WHERE display_name = 'Richard Hough'), 'malus1', 'If he changes his hat'),
((SELECT id FROM users WHERE display_name = 'Simone Gallo'), 'malus1', 'If he touches his beard'),
((SELECT id FROM users WHERE display_name = 'Simone Gallo'), 'malus2', 'If he "air plays" the drums'),
((SELECT id FROM users WHERE display_name = 'Veronica Pimazzon'), 'malus1', 'If she says "mammacara"');