CREATE DATABASE IF NOT EXISTS diamon_luxe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE diamon_luxe;

-- Table des catégories
CREATE TABLE categorie (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table des grades
CREATE TABLE grade (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(10) NOT NULL UNIQUE,
    nom VARCHAR(100) NOT NULL,
    description TEXT
) ENGINE=InnoDB;

-- Table des produits
CREATE TABLE produit (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_categorie INT NOT NULL,
    id_grade INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    marque VARCHAR(100) NOT NULL,
    description TEXT,
    prix DECIMAL(10,2) NOT NULL,
    image_principale VARCHAR(255),
    stock INT DEFAULT 1,
    disponible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categorie) REFERENCES categorie(id),
    FOREIGN KEY (id_grade) REFERENCES grade(id)
) ENGINE=InnoDB;

-- Table des images produits (multiples images par produit)
CREATE TABLE produit_image (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_produit INT NOT NULL,
    url_image VARCHAR(255) NOT NULL,
    ordre INT DEFAULT 0,
    FOREIGN KEY (id_produit) REFERENCES produit(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des clients
CREATE TABLE client (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    adresse TEXT,
    ville VARCHAR(100),
    code_postal VARCHAR(10),
    pays VARCHAR(100) DEFAULT 'France',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table des commandes
CREATE TABLE commande (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_client INT NOT NULL,
    numero_commande VARCHAR(50) NOT NULL UNIQUE,
    montant_total DECIMAL(10,2) NOT NULL,
    statut ENUM('en attente', 'payée', 'expédiée', 'livrée', 'annulée') DEFAULT 'en attente',
    adresse_livraison TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_client) REFERENCES client(id)
) ENGINE=InnoDB;

-- Table des détails de commande
CREATE TABLE commande_detail (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_commande INT NOT NULL,
    id_produit INT NOT NULL,
    quantite INT DEFAULT 1,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_commande) REFERENCES commande(id) ON DELETE CASCADE,
    FOREIGN KEY (id_produit) REFERENCES produit(id)
) ENGINE=InnoDB;

-- Insertion des grades
INSERT INTO grade (code, nom, description) VALUES
('A++', 'État Boutique', 'Neuf, jamais porté, avec certificat d\'origine et scellés.'),
('A+', 'État Exceptionnel', 'Proche du neuf, utilisé avec un soin extrême, accessoires inclus.'),
('A', 'Excellent État', 'Très légères marques d\'usage, structure et qualité préservées.');

-- Insertion des catégories
INSERT INTO categorie (nom, slug, description) VALUES
('Sacs', 'sacs', 'Sacs à main de luxe certifiés'),
('Montres', 'montres', 'Montres de prestige authentifiées'),
('Accessoires', 'accessoires', 'Accessoires de mode haut de gamme'),
('Bijoux', 'bijoux', 'Bijoux précieux et montres de luxe');

-- Insertion de produits d'exemple
INSERT INTO produit (id_categorie, id_grade, nom, marque, description, prix, image_principale, stock, disponible) VALUES
(1, 1, 'Sac Classique Box en Cuir', 'Celine', 'Sac iconique Celine Box en cuir lisse noir, état boutique avec certificat d\'authenticité et dustbag d\'origine.', 3400.00, 'celine-box.jpg', 1, TRUE),
(2, 2, 'Submariner Date "Hulk"', 'Rolex', 'Rolex Submariner Date 116610LV avec lunette verte emblématique. Boîte et papiers inclus.', 21900.00, 'rolex-hulk.jpg', 1, TRUE),
(1, 3, 'Pochette Kelly Cut Crocodile', 'Hermès', 'Hermès Kelly Cut en crocodile véritable, pièce rare en excellent état avec légères marques d\'usage.', 18500.00, 'hermes-kelly.jpg', 1, TRUE);