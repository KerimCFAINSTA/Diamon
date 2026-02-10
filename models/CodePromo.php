<?php
require_once __DIR__ . '/../includes/db.php';

class CodePromo {
    
    /**
     * Récupérer tous les codes promo
     */
    public static function getAll($actif_seulement = false) {
        global $pdo;
        
        try {
            $sql = "SELECT * FROM code_promo";
            if ($actif_seulement) {
                $sql .= " WHERE actif = 1";
            }
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur getAll codes promo: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer un code promo par ID
     */
    public static function getById($id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM code_promo WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur getById code promo: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer un code promo par CODE
     */
    public static function getByCode($code) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM code_promo WHERE code = ? AND actif = 1");
            $stmt->execute([strtoupper($code)]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur getByCode: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Créer un nouveau code promo
     */
    public static function creer($data) {
        global $pdo;
        
        try {
            $sql = "INSERT INTO code_promo (
                code, type, valeur, description, montant_minimum,
                date_debut, date_fin, limite_utilisation, actif
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                strtoupper($data['code']),
                $data['type'],
                $data['valeur'],
                $data['description'] ?? null,
                $data['montant_minimum'] ?? 0,
                $data['date_debut'] ?? null,
                $data['date_fin'] ?? null,
                $data['limite_utilisation'] ?? null,
                $data['actif'] ?? 1
            ]);
            
        } catch (PDOException $e) {
            error_log("Erreur création code promo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour un code promo
     */
    public static function update($id, $data) {
        global $pdo;
        
        try {
            $sql = "UPDATE code_promo SET 
                code = ?, 
                type = ?, 
                valeur = ?, 
                description = ?, 
                montant_minimum = ?,
                date_debut = ?,
                date_fin = ?,
                limite_utilisation = ?,
                actif = ?
                WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                strtoupper($data['code']),
                $data['type'],
                $data['valeur'],
                $data['description'] ?? null,
                $data['montant_minimum'] ?? 0,
                $data['date_debut'] ?? null,
                $data['date_fin'] ?? null,
                $data['limite_utilisation'] ?? null,
                $data['actif'] ?? 1,
                $id
            ]);
            
        } catch (PDOException $e) {
            error_log("Erreur mise à jour code promo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un code promo
     */
    public static function supprimer($id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("DELETE FROM code_promo WHERE id = ?");
            return $stmt->execute([$id]);
            
        } catch (PDOException $e) {
            error_log("Erreur suppression code promo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Valider un code promo
     */
    public static function valider($code, $montant_panier, $id_client = null) {
        $promo = self::getByCode($code);
        
        if (!$promo) {
            return ['valide' => false, 'message' => 'Code promo invalide'];
        }
        
        // Vérifier si actif
        if (!$promo['actif']) {
            return ['valide' => false, 'message' => 'Ce code promo n\'est plus actif'];
        }
        
        // Vérifier les dates
        $aujourdhui = date('Y-m-d');
        
        if ($promo['date_debut'] && $aujourdhui < $promo['date_debut']) {
            return ['valide' => false, 'message' => 'Ce code promo n\'est pas encore valide'];
        }
        
        if ($promo['date_fin'] && $aujourdhui > $promo['date_fin']) {
            return ['valide' => false, 'message' => 'Ce code promo a expiré'];
        }
        
        // Vérifier le montant minimum
        if ($montant_panier < $promo['montant_minimum']) {
            return [
                'valide' => false, 
                'message' => 'Montant minimum requis : ' . number_format($promo['montant_minimum'], 2, ',', ' ') . ' €'
            ];
        }
        
        // Vérifier la limite d'utilisation
        if ($promo['limite_utilisation'] && $promo['nb_utilisations'] >= $promo['limite_utilisation']) {
            return ['valide' => false, 'message' => 'Ce code promo a atteint sa limite d\'utilisation'];
        }
        
        // Calculer la réduction
        $reduction = 0;
        if ($promo['type'] === 'pourcentage') {
            $reduction = ($montant_panier * $promo['valeur']) / 100;
        } else {
            $reduction = $promo['valeur'];
        }
        
        // La réduction ne peut pas dépasser le montant du panier
        $reduction = min($reduction, $montant_panier);
        
        return [
            'valide' => true,
            'promo' => $promo,
            'reduction' => $reduction,
            'message' => 'Code promo appliqué avec succès !'
        ];
    }
    
    /**
     * Enregistrer l'utilisation d'un code promo
     */
    public static function enregistrerUtilisation($id_promo, $id_client, $montant_reduction, $id_commande = null) {
        global $pdo;
        
        try {
            // Enregistrer l'utilisation
            $stmt = $pdo->prepare("
                INSERT INTO code_promo_utilisation (id_code_promo, id_client, id_commande, montant_reduction)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$id_promo, $id_client, $id_commande, $montant_reduction]);
            
            // Incrémenter le compteur
            $stmt = $pdo->prepare("UPDATE code_promo SET nb_utilisations = nb_utilisations + 1 WHERE id = ?");
            $stmt->execute([$id_promo]);
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Erreur enregistrement utilisation: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir les statistiques d'un code promo
     */
    public static function getStatistiques($id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as nb_utilisations,
                    SUM(montant_reduction) as total_reductions,
                    AVG(montant_reduction) as moyenne_reduction
                FROM code_promo_utilisation
                WHERE id_code_promo = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur stats code promo: " . $e->getMessage());
            return null;
        }
    }
}