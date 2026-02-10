<?php
require_once __DIR__ . '/../includes/db.php';

class Client {
    
    /**
     * Inscription d'un nouveau client
     */
    public static function creer($nom, $prenom, $email, $mot_de_passe, $telephone = null) {
        global $pdo;
        
        // Vérifier si l'email existe déjà
        if (self::emailExiste($email)) {
            return false;
        }
        
        // Hasher le mot de passe
        $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO client (nom, prenom, email, mot_de_passe, telephone)
            VALUES (:nom, :prenom, :email, :mot_de_passe, :telephone)
        ");
        
        return $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'mot_de_passe' => $mot_de_passe_hash,
            'telephone' => $telephone
        ]);
    }
    
    /**
     * Connexion d'un client
     */
    public static function connexion($email, $mot_de_passe) {
        global $pdo;
        
        $stmt = $pdo->prepare("SELECT * FROM client WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $client = $stmt->fetch();
        
        if ($client && password_verify($mot_de_passe, $client['mot_de_passe'])) {
            return $client;
        }
        
        return false;
    }
    
    /**
     * Récupère un client par son ID
     */
    public static function getById($id) {
        global $pdo;
        
        $stmt = $pdo->prepare("SELECT * FROM client WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Vérifie si un email existe déjà
     */
    public static function emailExiste($email) {
        global $pdo;
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM client WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Met à jour les informations d'un client
     */
    public static function mettreAJour($id, $data) {
        global $pdo;
        
        $fields = [];
        $params = ['id' => $id];
        
        foreach ($data as $key => $value) {
            if ($key !== 'id' && $key !== 'mot_de_passe' && $key !== 'email') {
                $fields[] = "$key = :$key";
                $params[$key] = $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE client SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        return $stmt->execute($params);
    }
    
    /**
     * Change le mot de passe d'un client
     */
    public static function changerMotDePasse($id, $ancien_mdp, $nouveau_mdp) {
        global $pdo;
        
        $client = self::getById($id);
        
        if (!$client || !password_verify($ancien_mdp, $client['mot_de_passe'])) {
            return false;
        }
        
        $nouveau_hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE client SET mot_de_passe = :mdp WHERE id = :id");
        return $stmt->execute(['mdp' => $nouveau_hash, 'id' => $id]);
    }
}