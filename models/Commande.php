<?php
require_once __DIR__ . '/../includes/db.php';

class Commande {
    
    /**
     * Crée une nouvelle commande
     */
    public static function creer($id_client, $produits, $adresse_livraison) {
        global $pdo;
        
        try {
            $pdo->beginTransaction();
            
            // Générer un numéro de commande unique
            $numero_commande = 'DIA-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            // Calculer le montant total
            $montant_total = 0;
            foreach ($produits as $produit) {
                $montant_total += $produit['prix'] * $produit['quantite'];
            }
            
            // Insérer la commande
            $stmt = $pdo->prepare("
                INSERT INTO commande (id_client, numero_commande, montant_total, adresse_livraison)
                VALUES (:id_client, :numero_commande, :montant_total, :adresse_livraison)
            ");
            
            $stmt->execute([
                'id_client' => $id_client,
                'numero_commande' => $numero_commande,
                'montant_total' => $montant_total,
                'adresse_livraison' => $adresse_livraison
            ]);
            
            $id_commande = $pdo->lastInsertId();
            
            // Insérer les détails de la commande
            $stmt_detail = $pdo->prepare("
                INSERT INTO commande_detail (id_commande, id_produit, quantite, prix_unitaire)
                VALUES (:id_commande, :id_produit, :quantite, :prix_unitaire)
            ");
            
            foreach ($produits as $produit) {
                $stmt_detail->execute([
                    'id_commande' => $id_commande,
                    'id_produit' => $produit['id'],
                    'quantite' => $produit['quantite'],
                    'prix_unitaire' => $produit['prix']
                ]);
                
                // Mettre à jour le stock
                $stmt_stock = $pdo->prepare("
                    UPDATE produit 
                    SET stock = stock - :quantite,
                        disponible = CASE WHEN stock - :quantite <= 0 THEN 0 ELSE 1 END
                    WHERE id = :id_produit
                ");
                
                $stmt_stock->execute([
                    'quantite' => $produit['quantite'],
                    'id_produit' => $produit['id']
                ]);
            }
            
            $pdo->commit();
            
            return [
                'success' => true,
                'id_commande' => $id_commande,
                'numero_commande' => $numero_commande
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Récupère les commandes d'un client
     */
    public static function getByClient($id_client) {
        global $pdo;
        
        $stmt = $pdo->prepare("
            SELECT * FROM commande 
            WHERE id_client = :id_client 
            ORDER BY created_at DESC
        ");
        
        $stmt->execute(['id_client' => $id_client]);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère une commande par son ID
     */
    public static function getById($id) {
        global $pdo;
        
        $stmt = $pdo->prepare("
            SELECT c.*, cl.nom, cl.prenom, cl.email, cl.telephone
            FROM commande c
            JOIN client cl ON c.id_client = cl.id
            WHERE c.id = :id
        ");
        
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Récupère les détails d'une commande
     */
    public static function getDetails($id_commande) {
        global $pdo;
        
        $stmt = $pdo->prepare("
            SELECT cd.*, p.nom, p.marque, p.image_principale
            FROM commande_detail cd
            JOIN produit p ON cd.id_produit = p.id
            WHERE cd.id_commande = :id_commande
        ");
        
        $stmt->execute(['id_commande' => $id_commande]);
        return $stmt->fetchAll();
    }
    
    /**
     * Met à jour le statut d'une commande
     */
    public static function updateStatut($id, $statut) {
        global $pdo;
        
        $statuts_valides = ['en attente', 'payée', 'expédiée', 'livrée', 'annulée'];
        
        if (!in_array($statut, $statuts_valides)) {
            return false;
        }
        
        $stmt = $pdo->prepare("UPDATE commande SET statut = :statut WHERE id = :id");
        return $stmt->execute(['statut' => $statut, 'id' => $id]);
    }
    
    /**
     * Récupère toutes les commandes (admin)
     */
    public static function getAll($limit = null) {
        global $pdo;
        
        $sql = "
            SELECT c.*, cl.nom, cl.prenom, cl.email
            FROM commande c
            JOIN client cl ON c.id_client = cl.id
            ORDER BY c.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $pdo->prepare($sql);
        
        if ($limit) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
}