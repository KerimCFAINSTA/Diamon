<?php
require_once __DIR__ . '/../includes/db.php';

class Wishlist {
    
    /**
     * Ajouter un produit à la wishlist
     */
    public static function ajouter($id_client, $id_produit) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO wishlist (id_client, id_produit)
                VALUES (?, ?)
            ");
            
            return $stmt->execute([$id_client, $id_produit]);
            
        } catch (PDOException $e) {
            error_log("Erreur ajout wishlist: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retirer un produit de la wishlist
     */
    public static function retirer($id_client, $id_produit) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                DELETE FROM wishlist 
                WHERE id_client = ? AND id_produit = ?
            ");
            
            return $stmt->execute([$id_client, $id_produit]);
            
        } catch (PDOException $e) {
            error_log("Erreur retrait wishlist: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifier si un produit est dans la wishlist
     */
    public static function estDansWishlist($id_client, $id_produit) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM wishlist
                WHERE id_client = ? AND id_produit = ?
            ");
            
            $stmt->execute([$id_client, $id_produit]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Erreur vérif wishlist: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir tous les produits de la wishlist d'un client
     */
    public static function getProduitsWishlist($id_client) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    p.*,
                    c.nom as categorie_nom,
                    c.slug as categorie_slug,
                    g.code as grade_code,
                    g.nom as grade_nom,
                    w.created_at as date_ajout,
                    (SELECT AVG(note) FROM avis WHERE id_produit = p.id AND statut = 'approuve') as note_moyenne,
                    (SELECT COUNT(*) FROM avis WHERE id_produit = p.id AND statut = 'approuve') as nb_avis
                FROM wishlist w
                JOIN produit p ON w.id_produit = p.id
                LEFT JOIN categorie c ON p.id_categorie = c.id
                LEFT JOIN grade g ON p.id_grade = g.id
                WHERE w.id_client = ?
                ORDER BY w.created_at DESC
            ");
            
            $stmt->execute([$id_client]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur get wishlist: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Compter le nombre de produits dans la wishlist
     */
    public static function compter($id_client) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM wishlist
                WHERE id_client = ?
            ");
            
            $stmt->execute([$id_client]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return intval($result['count']);
            
        } catch (PDOException $e) {
            error_log("Erreur comptage wishlist: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Vider la wishlist
     */
    public static function vider($id_client) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("DELETE FROM wishlist WHERE id_client = ?");
            return $stmt->execute([$id_client]);
            
        } catch (PDOException $e) {
            error_log("Erreur vidage wishlist: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Déplacer tous les produits de la wishlist vers le panier
     */
    public static function deplacerVersPanier($id_client) {
        global $pdo;
        
        try {
            // Récupérer les produits de la wishlist
            $produits = self::getProduitsWishlist($id_client);
            
            require_once __DIR__ . '/Panier.php';
            
            $success_count = 0;
            foreach ($produits as $produit) {
                if ($produit['disponible'] && $produit['stock'] > 0) {
                    if (Panier::ajouter($id_client, $produit['id'], 1)) {
                        self::retirer($id_client, $produit['id']);
                        $success_count++;
                    }
                }
            }
            
            return $success_count;
            
        } catch (Exception $e) {
            error_log("Erreur déplacement wishlist vers panier: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtenir les produits populaires dans les wishlists
     */
    public static function getProduitsPopulaires($limit = 10) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    p.*,
                    c.nom as categorie_nom,
                    g.code as grade_code,
                    COUNT(w.id) as nb_wishlists
                FROM wishlist w
                JOIN produit p ON w.id_produit = p.id
                LEFT JOIN categorie c ON p.id_categorie = c.id
                LEFT JOIN grade g ON p.id_grade = g.id
                WHERE p.disponible = 1
                GROUP BY p.id
                ORDER BY nb_wishlists DESC
                LIMIT ?
            ");
            
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur produits populaires wishlist: " . $e->getMessage());
            return [];
        }
    }
}