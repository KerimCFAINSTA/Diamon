<?php
require_once __DIR__ . '/../includes/db.php';

class DemandeVente {
    
    /**
     * Crée une nouvelle demande de vente
     */
    public static function creer($data) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO demande_vente (
                    id_client, marque, nom_produit, categorie, etat_estime, 
                    description, prix_souhaite, annee_achat, 
                    avec_boite, avec_certificat, avec_accessoires,
                    photo_1, photo_2, photo_3, photo_4
                ) VALUES (
                    :id_client, :marque, :nom_produit, :categorie, :etat_estime,
                    :description, :prix_souhaite, :annee_achat,
                    :avec_boite, :avec_certificat, :avec_accessoires,
                    :photo_1, :photo_2, :photo_3, :photo_4
                )
            ");
            
            return $stmt->execute($data);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Récupère les demandes d'un client
     */
    public static function getByClient($id_client) {
        global $pdo;
        
        $stmt = $pdo->prepare("
            SELECT * FROM demande_vente 
            WHERE id_client = :id_client 
            ORDER BY created_at DESC
        ");
        
        $stmt->execute(['id_client' => $id_client]);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère toutes les demandes (admin)
     */
    public static function getAll($statut = null) {
        global $pdo;
        
        $sql = "
            SELECT dv.*, c.nom, c.prenom, c.email, c.telephone
            FROM demande_vente dv
            JOIN client c ON dv.id_client = c.id
        ";
        
        if ($statut) {
            $sql .= " WHERE dv.statut = :statut";
        }
        
        $sql .= " ORDER BY dv.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        
        if ($statut) {
            $stmt->execute(['statut' => $statut]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère une demande par son ID
     */
    public static function getById($id) {
        global $pdo;
        
        $stmt = $pdo->prepare("
            SELECT dv.*, c.nom, c.prenom, c.email, c.telephone
            FROM demande_vente dv
            JOIN client c ON dv.id_client = c.id
            WHERE dv.id = :id
        ");
        
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Met à jour le statut d'une demande
     */
    public static function updateStatut($id, $statut, $estimation = null, $commentaire = null) {
        global $pdo;
        
        $stmt = $pdo->prepare("
            UPDATE demande_vente 
            SET statut = :statut, 
                estimation_expert = :estimation, 
                commentaire_expert = :commentaire
            WHERE id = :id
        ");
        
        return $stmt->execute([
            'statut' => $statut,
            'estimation' => $estimation,
            'commentaire' => $commentaire,
            'id' => $id
        ]);
    }
}