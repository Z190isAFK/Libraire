-- Créer la base de données 'librairie' si elle n'existe pas
CREATE DATABASE IF NOT EXISTS librairie;

-- Sélectionner la base de données pour les opérations suivantes
USE librairie;

-- Table des utilisateurs : stocke les informations des utilisateurs
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,              -- Identifiant unique auto-incrémenté
    username VARCHAR(50) NOT NULL UNIQUE,           -- Nom d'utilisateur (unique)
    email VARCHAR(100) NOT NULL UNIQUE,             -- Adresse email (unique)
    password VARCHAR(255) NOT NULL,                 -- Mot de passe haché
    role ENUM('lecteur', 'administrateur') DEFAULT 'lecteur' -- Rôle de l'utilisateur
);

-- Table des livres : stocke les informations des livres
CREATE TABLE livres (
    id INT AUTO_INCREMENT PRIMARY KEY,              -- Identifiant unique auto-incrémenté
    titre VARCHAR(100) NOT NULL,                    -- Titre du livre
    serie VARCHAR(50) NOT NULL,                     -- Série (Marvel, DC Comics, Invincible)
    numero INT NOT NULL,                            -- Numéro dans la série
    description TEXT NOT NULL,                      -- Description du livre
    image VARCHAR(255) NOT NULL,                    -- Nom du fichier image (sans chemin)
    disponible TINYINT(1) DEFAULT 1                 -- Disponibilité (1 = disponible, 0 = emprunté)
);

-- Table des prêts : stocke les informations des emprunts
CREATE TABLE prets (
    id INT AUTO_INCREMENT PRIMARY KEY,              -- Identifiant unique auto-incrémenté
    utilisateur_id INT NOT NULL,                    -- ID de l'utilisateur qui emprunte
    livre_id INT NOT NULL,                          -- ID du livre emprunté
    date_debut DATE NOT NULL,                       -- Date de début de l'emprunt
    date_fin DATE DEFAULT NULL,                     -- Date de fin de l'emprunt (NULL si en cours)
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE, -- Lien avec utilisateurs
    FOREIGN KEY (livre_id) REFERENCES livres(id) ON DELETE CASCADE              -- Lien avec livres
);

-- Insérer des utilisateurs initiaux
-- Mot de passe : 'admin123' (haché avec password_hash, à remplacer par une valeur réelle)
INSERT INTO utilisateurs (username, email, password, role) VALUES
('admin', 'admin@example.com', '$2y$10$3z9kXqL7Xz1fX7z9kXqL7Xz1fX7z9kXqL7Xz1fX7z9kXqL7Xz1fX', 'administrateur'),
('lecteur1', 'lecteur1@example.com', '$2y$10$3z9kXqL7Xz1fX7z9kXqL7Xz1fX7z9kXqL7Xz1fX7z9kXqL7Xz1fX', 'lecteur');

-- Insérer des livres initiaux (images fictives, à remplacer par des fichiers réels)
INSERT INTO livres (titre, serie, numero, description, image, disponible) VALUES
('Marvel #1', 'Marvel', 1, 'Première aventure épique de Marvel.', 'marvel-1.jpg', 1),
('Marvel #2', 'Marvel', 2, 'Suite des aventures Marvel.', 'marvel-2.jpg', 1),
('Marvel #3', 'Marvel', 3, 'Troisième tome de la série Marvel.', 'marvel-3.jpg', 1),
('DC Comics #1', 'DC Comics', 1, 'Première aventure de DC Comics.', 'dc-1.jpg', 1),
('DC Comics #2', 'DC Comics', 2, 'Suite des aventures DC Comics.', 'dc-2.jpg', 1),
('Invincible #1', 'Invincible', 1, 'Première aventure d’Invincible.', 'invincible-1.jpg', 1),
('Invincible #2', 'Invincible', 2, 'Suite des aventures d’Invincible.', 'invincible-2.jpg', 1);

-- Insérer un exemple de prêt
INSERT INTO prets (utilisateur_id, livre_id, date_debut) VALUES
(2, 1, '2025-03-10');

-- Fin du script SQL