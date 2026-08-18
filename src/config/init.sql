SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- Table users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255),
    age INT,
    sexe ENUM('homme','femme','autre') DEFAULT 'autre',
    role ENUM('client','admin','employe') DEFAULT 'client',
    google_id VARCHAR(255) DEFAULT NULL,
    facebook_id VARCHAR(255) DEFAULT NULL,
    newsletter TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table tickets
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    gain ENUM('infuseur','the_detox','the_signature','coffret_39','coffret_69') NOT NULL,
    utilise TINYINT(1) DEFAULT 0,
    user_id INT DEFAULT NULL,
    remis TINYINT(1) DEFAULT 0,
    date_utilisation TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table tirage_final
CREATE TABLE IF NOT EXISTS tirage_final (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gagnant TINYINT(1) DEFAULT 0,
    date_tirage TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Compte admin par défaut
-- Email: admin@thetiptop.fr / Mot de passe: Admin2024!
INSERT IGNORE INTO users (nom, prenom, email, password, role)
VALUES ('Admin', 'Système', 'admin@thetiptop.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Compte employé par défaut
-- Email: employe@thetiptop.fr / Mot de passe: Employe2024!
INSERT IGNORE INTO users (nom, prenom, email, password, role)
VALUES ('Employé', 'Boutique', 'employe@thetiptop.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employe');
