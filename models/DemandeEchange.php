<?php
require_once __DIR__ . '/../includes/db.php';

class DemandeEchange {
    
    /**
     * Crée une nouvelle demande d'échange
     */
    public static function creer($data) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO demande_echange (
                    id_client, id_produit_souhaite,
                    marque_propose, nom_produit_propose, categorie_propose, 
                    etat_estime, description_propose, valeur_estimee, annee_achat,
                    avec_boite, avec_certificat, avec_accessoires,
                    photo_1, photo_2, photo_3, photo_4
                ) VALUES (
                    :id_client, :id_produit_souhaite,
                    :marque_propose, :nom_produit_propose, :categorie_propose,
                    :etat_estime, :description_propose, :valeur_estimee, :annee_achat,
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
            SELECT de.*, p.nom as produit_souhaite_nom, p.marque as produit_souhaite_marque, 
                   p.prix as produit_souhaite_prix, p.image_principale
            FROM demande_echange de
            JOIN produit p ON de.id_produit_souhaite = p.id
            WHERE de.id_client = :id_client 
            ORDER BY de.created_at DESC
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
            SELECT de.*, c.nom, c.prenom, c.email, c.telephone,
                   p.nom as produit_souhaite_nom, p.marque as produit_souhaite_marque
            FROM demande_echange de
            JOIN client c ON de.id_client = c.id
            JOIN produit p ON de.id_produit_souhaite = p.id
        ";
        
        if ($statut) {
            $sql .= " WHERE de.statut = :statut";
        }
        
        $sql .= " ORDER BY de.created_at DESC";
        
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
            SELECT de.*, c.nom, c.prenom, c.email, c.telephone,
                   p.nom as produit_souhaite_nom, p.marque as produit_souhaite_marque, 
                   p.prix as produit_souhaite_prix
            FROM demande_echange de
            JOIN client c ON de.id_client = c.id
            JOIN produit p ON de.id_produit_souhaite = p.id
            WHERE de.id = :id
        ");
        
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Met à jour le statut d'une demande
     */
    public static function updateStatut($id, $statut, $evaluation = null, $difference = null, $commentaire = null) {
        global $pdo;
        
        $stmt = $pdo->prepare("
            UPDATE demande_echange 
            SET statut = :statut, 
                evaluation_expert = :evaluation, 
                difference_prix = :difference,
                commentaire_expert = :commentaire
            WHERE id = :id
        ");
        
        return $stmt->execute([
            'statut' => $statut,
            'evaluation' => $evaluation,
            'difference' => $difference,
            'commentaire' => $commentaire,
            'id' => $id
        ]);
    }
}