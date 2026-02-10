<?php
require_once __DIR__ . '/../includes/db.php';

class Categorie {
    
    /**
     * Récupère toutes les catégories
     */
    public static function getAll() {
        global $pdo;
        
        $stmt = $pdo->query("
            SELECT c.*, COUNT(p.id) as nb_produits
            FROM categorie c
            LEFT JOIN produit p ON c.id = p.id_categorie AND p.disponible = 1
            GROUP BY c.id
            ORDER BY c.nom ASC
        ");
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère une catégorie par son slug
     */
    public static function getBySlug($slug) {
        global $pdo;
        
        $stmt = $pdo->prepare("SELECT * FROM categorie WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch();
    }
    
    /**
     * Récupère une catégorie par son ID
     */
    public static function getById($id) {
        global $pdo;
        
        $stmt = $pdo->prepare("SELECT * FROM categorie WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}