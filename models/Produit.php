<?php
require_once __DIR__ . '/../includes/db.php';

class Produit {
    
    /**
     * Récupère tous les produits disponibles avec filtres optionnels
     */
    public static function getAll($categorie = null, $grade = null, $limit = null) {
        global $pdo;
        
        $sql = "SELECT p.*, c.nom as categorie_nom, c.slug as categorie_slug, 
                       g.code as grade_code, g.nom as grade_nom
                FROM produit p
                JOIN categorie c ON p.id_categorie = c.id
                JOIN grade g ON p.id_grade = g.id
                WHERE p.disponible = 1";
        
        $params = [];
        
        if ($categorie) {
            $sql .= " AND c.slug = :categorie";
            $params['categorie'] = $categorie;
        }
        
        if ($grade) {
            $sql .= " AND g.code = :grade";
            $params['grade'] = $grade;
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        if ($limit) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère un produit par son ID
     */
    public static function getById($id) {
        global $pdo;
        
        $stmt = $pdo->prepare("
            SELECT p.*, c.nom as categorie_nom, c.slug as categorie_slug,
                   g.code as grade_code, g.nom as grade_nom, g.description as grade_description
            FROM produit p
            JOIN categorie c ON p.id_categorie = c.id
            JOIN grade g ON p.id_grade = g.id
            WHERE p.id = :id
        ");
        
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Récupère les images d'un produit
     */
    public static function getImages($id_produit) {
        global $pdo;
        
        $stmt = $pdo->prepare("
            SELECT * FROM produit_image 
            WHERE id_produit = :id_produit 
            ORDER BY ordre ASC
        ");
        
        $stmt->execute(['id_produit' => $id_produit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Recherche de produits
     */
    public static function search($terme) {
        global $pdo;
        
        $stmt = $pdo->prepare("
            SELECT p.*, c.nom as categorie_nom, g.code as grade_code
            FROM produit p
            JOIN categorie c ON p.id_categorie = c.id
            JOIN grade g ON p.id_grade = g.id
            WHERE p.disponible = 1 
            AND (p.nom LIKE :terme OR p.marque LIKE :terme OR p.description LIKE :terme)
            ORDER BY p.created_at DESC
        ");
        
        $terme = "%$terme%";
        $stmt->execute(['terme' => $terme]);
        return $stmt->fetchAll();
    }
    
    /**
     * Vérifie la disponibilité d'un produit
     */
    public static function estDisponible($id) {
        global $pdo;
        
        $stmt = $pdo->prepare("SELECT disponible, stock FROM produit WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $produit = $stmt->fetch();
        
        return $produit && $produit['disponible'] == 1 && $produit['stock'] > 0;
    }

    
    
    // ... (tes méthodes existantes)
    
    /**
     * Créer un nouveau produit
     */
    public static function creer($data) {
        global $pdo;
        
        try {
            $sql = "INSERT INTO produit (
                marque, nom, description, prix, stock, 
                id_categorie, id_grade, disponible, 
                image_principale, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                $data['marque'],
                $data['nom'],
                $data['description'],
                $data['prix'],
                $data['stock'],
                $data['id_categorie'],
                $data['id_grade'],
                $data['disponible'] ?? 1,
                $data['image_principale'] ?? null
            ]);
            
        } catch (PDOException $e) {
            error_log("Erreur création produit: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour un produit
     */
    public static function update($id, $data) {
        global $pdo;
        
        try {
            $sql = "UPDATE produit SET 
                marque = ?, 
                nom = ?, 
                description = ?, 
                prix = ?, 
                stock = ?, 
                id_categorie = ?, 
                id_grade = ?, 
                disponible = ?";
            
            $params = [
                $data['marque'],
                $data['nom'],
                $data['description'],
                $data['prix'],
                $data['stock'],
                $data['id_categorie'],
                $data['id_grade'],
                $data['disponible']
            ];
            
            // Si nouvelle image principale
            if (!empty($data['image_principale'])) {
                $sql .= ", image_principale = ?";
                $params[] = $data['image_principale'];
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $pdo->prepare($sql);
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Erreur mise à jour produit: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un produit
     */
    public static function supprimer($id) {
        global $pdo;
        
        try {
            // Supprimer les images associées
            $stmt = $pdo->prepare("SELECT chemin FROM produit_image WHERE id_produit = ?");
            $stmt->execute([$id]);
            $images = $stmt->fetchAll();
            
            foreach ($images as $img) {
                $filepath = __DIR__ . '/../public/images/produits/' . $img['chemin'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
            
            // Supprimer le produit (les images seront supprimées par CASCADE)
            $stmt = $pdo->prepare("DELETE FROM produit WHERE id = ?");
            return $stmt->execute([$id]);
            
        } catch (PDOException $e) {
            error_log("Erreur suppression produit: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ajouter des images supplémentaires à un produit
     */
    public static function ajouterImages($id_produit, $images) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("INSERT INTO produit_image (id_produit, chemin) VALUES (?, ?)");
            
            foreach ($images as $image) {
                $stmt->execute([$id_produit, $image]);
            }
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Erreur ajout images: " . $e->getMessage());
            return false;
        }
    }

}