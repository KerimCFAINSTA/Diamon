<?php
require_once __DIR__ . '/../includes/db.php';

class Panier {
    
    /**
     * Ajouter un produit au panier
     */
    public static function ajouter($id_client, $id_produit, $quantite = 1) {
        global $pdo;
        
        try {
            // Vérifier si le produit existe déjà dans le panier
            $stmt = $pdo->prepare("SELECT id, quantite FROM panier WHERE id_client = ? AND id_produit = ?");
            $stmt->execute([$id_client, $id_produit]);
            $existant = $stmt->fetch();
            
            if ($existant) {
                // Mettre à jour la quantité
                $nouvelle_quantite = $existant['quantite'] + $quantite;
                $stmt = $pdo->prepare("UPDATE panier SET quantite = ? WHERE id = ?");
                return $stmt->execute([$nouvelle_quantite, $existant['id']]);
            } else {
                // Insérer un nouveau produit
                $stmt = $pdo->prepare("INSERT INTO panier (id_client, id_produit, quantite) VALUES (?, ?, ?)");
                return $stmt->execute([$id_client, $id_produit, $quantite]);
            }
            
        } catch (PDOException $e) {
            error_log("Erreur ajout panier: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retirer un produit du panier
     */
    public static function retirer($id_client, $id_produit) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("DELETE FROM panier WHERE id_client = ? AND id_produit = ?");
            return $stmt->execute([$id_client, $id_produit]);
            
        } catch (PDOException $e) {
            error_log("Erreur retrait panier: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Modifier la quantité d'un produit
     */
    public static function modifierQuantite($id_client, $id_produit, $quantite) {
        global $pdo;
        
        try {
            if ($quantite <= 0) {
                return self::retirer($id_client, $id_produit);
            }
            
            $stmt = $pdo->prepare("UPDATE panier SET quantite = ? WHERE id_client = ? AND id_produit = ?");
            return $stmt->execute([$quantite, $id_client, $id_produit]);
            
        } catch (PDOException $e) {
            error_log("Erreur modification quantité: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer les articles du panier
     */
    public static function getArticles($id_client) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    p.id as id_produit,
                    p.marque,
                    p.nom,
                    p.prix,
                    p.image_principale,
                    p.stock,
                    pa.quantite,
                    pa.id as panier_id
                FROM panier pa
                JOIN produit p ON pa.id_produit = p.id
                WHERE pa.id_client = ?
                ORDER BY pa.created_at DESC
            ");
            
            $stmt->execute([$id_client]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur get articles panier: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Compter les articles dans le panier
     */
    public static function compter($id_client) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("SELECT SUM(quantite) as total FROM panier WHERE id_client = ?");
            $stmt->execute([$id_client]);
            $result = $stmt->fetch();
            
            return intval($result['total'] ?? 0);
            
        } catch (PDOException $e) {
            error_log("Erreur comptage panier: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calculer le sous-total (avant réduction)
     */
    public static function getSousTotal($id_client) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT SUM(p.prix * pa.quantite) as sous_total
                FROM panier pa
                JOIN produit p ON pa.id_produit = p.id
                WHERE pa.id_client = ?
            ");
            
            $stmt->execute([$id_client]);
            $result = $stmt->fetch();
            
            return floatval($result['sous_total'] ?? 0);
            
        } catch (PDOException $e) {
            error_log("Erreur calcul sous-total: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Appliquer un code promo au panier
     */
    public static function appliquerCodePromo($id_client, $code_promo) {
        global $pdo;
        
        try {
            require_once __DIR__ . '/CodePromo.php';
            
            // Calculer le sous-total du panier
            $sous_total = self::getSousTotal($id_client);
            
            // Valider le code promo
            $validation = CodePromo::valider($code_promo, $sous_total, $id_client);
            
            if (!$validation['valide']) {
                return ['success' => false, 'message' => $validation['message']];
            }
            
            // Appliquer le code promo à tous les articles du panier
            $stmt = $pdo->prepare("
                UPDATE panier 
                SET id_code_promo = ?, montant_reduction = ?
                WHERE id_client = ?
            ");
            
            $stmt->execute([
                $validation['promo']['id'],
                $validation['reduction'],
                $id_client
            ]);
            
            return [
                'success' => true,
                'message' => $validation['message'],
                'reduction' => $validation['reduction'],
                'promo' => $validation['promo']
            ];
            
        } catch (PDOException $e) {
            error_log("Erreur application code promo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors de l\'application du code promo'];
        }
    }
    
    /**
     * Retirer le code promo du panier
     */
    public static function retirerCodePromo($id_client) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                UPDATE panier 
                SET id_code_promo = NULL, montant_reduction = 0
                WHERE id_client = ?
            ");
            
            return $stmt->execute([$id_client]);
            
        } catch (PDOException $e) {
            error_log("Erreur retrait code promo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir le code promo appliqué
     */
    public static function getCodePromoApplique($id_client) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT DISTINCT cp.*, p.montant_reduction
                FROM panier p
                JOIN code_promo cp ON p.id_code_promo = cp.id
                WHERE p.id_client = ? AND p.id_code_promo IS NOT NULL
                LIMIT 1
            ");
            
            $stmt->execute([$id_client]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur get code promo: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Calculer le total final (après réduction)
     */
    public static function getTotal($id_client) {
        $sous_total = self::getSousTotal($id_client);
        $code_promo = self::getCodePromoApplique($id_client);
        
        $reduction = 0;
        if ($code_promo) {
            $reduction = floatval($code_promo['montant_reduction']);
        }
        
        return max(0, $sous_total - $reduction);
    }
    
    /**
     * Obtenir le contenu complet du panier avec totaux
     */
    public static function getContenuComplet($id_client) {
        $articles = self::getArticles($id_client);
        $sous_total = self::getSousTotal($id_client);
        $code_promo = self::getCodePromoApplique($id_client);
        $total = self::getTotal($id_client);
        
        return [
            'articles' => $articles,
            'sous_total' => $sous_total,
            'code_promo' => $code_promo,
            'reduction' => $code_promo ? floatval($code_promo['montant_reduction']) : 0,
            'total' => $total,
            'nb_articles' => count($articles)
        ];
    }
    
    /**
     * Vider le panier
     */
    public static function vider($id_client) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("DELETE FROM panier WHERE id_client = ?");
            return $stmt->execute([$id_client]);
            
        } catch (PDOException $e) {
            error_log("Erreur vidage panier: " . $e->getMessage());
            return false;
        }
    }
}