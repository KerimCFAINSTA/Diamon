<?php
require_once __DIR__ . '/../includes/db.php';

class Avis {
    
    /**
     * Créer un nouvel avis
     */
    public static function creer($data) {
        global $pdo;
        
        try {
            $sql = "INSERT INTO avis (
                id_produit, id_client, id_commande, note, titre, 
                commentaire, date_achat, recommande, verifie
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $data['id_produit'],
                $data['id_client'],
                $data['id_commande'],
                $data['note'],
                $data['titre'] ?? null,
                $data['commentaire'] ?? null,
                $data['date_achat'] ?? null,
                $data['recommande'] ?? 1,
                isset($data['id_commande']) ? 1 : 0 // Vérifié si achat confirmé
            ]);
            
            if ($result) {
                return $pdo->lastInsertId();
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Erreur création avis: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ajouter des photos à un avis
     */
    public static function ajouterPhotos($id_avis, $photos) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("INSERT INTO avis_photo (id_avis, chemin) VALUES (?, ?)");
            
            foreach ($photos as $photo) {
                $stmt->execute([$id_avis, $photo]);
            }
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Erreur ajout photos avis: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer les avis d'un produit (approuvés uniquement)
     */
    public static function getParProduit($id_produit, $limit = null) {
        global $pdo;
        
        try {
            $sql = "
                SELECT 
                    a.*,
                    c.prenom,
                    c.nom,
                    (SELECT COUNT(*) FROM avis_vote WHERE id_avis = a.id AND type = 'utile') as votes_utiles,
                    (SELECT COUNT(*) FROM avis_vote WHERE id_avis = a.id AND type = 'inutile') as votes_inutiles
                FROM avis a
                JOIN client c ON a.id_client = c.id
                WHERE a.id_produit = ? AND a.statut = 'approuve'
                ORDER BY a.created_at DESC
            ";
            
            if ($limit) {
                $sql .= " LIMIT " . intval($limit);
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_produit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur get avis produit: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer les photos d'un avis
     */
    public static function getPhotos($id_avis) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM avis_photo WHERE id_avis = ?");
            $stmt->execute([$id_avis]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur get photos avis: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calculer la note moyenne d'un produit
     */
    public static function getNoteMoyenne($id_produit) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    AVG(note) as moyenne,
                    COUNT(*) as nb_avis
                FROM avis
                WHERE id_produit = ? AND statut = 'approuve'
            ");
            $stmt->execute([$id_produit]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'moyenne' => $result['moyenne'] ? round($result['moyenne'], 1) : 0,
                'nb_avis' => intval($result['nb_avis'])
            ];
            
        } catch (PDOException $e) {
            error_log("Erreur calcul note moyenne: " . $e->getMessage());
            return ['moyenne' => 0, 'nb_avis' => 0];
        }
    }
    
    /**
     * Répartition des notes d'un produit
     */
    public static function getRepartitionNotes($id_produit) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    note,
                    COUNT(*) as count
                FROM avis
                WHERE id_produit = ? AND statut = 'approuve'
                GROUP BY note
                ORDER BY note DESC
            ");
            $stmt->execute([$id_produit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur répartition notes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Vérifier si le client peut laisser un avis
     */
    public static function peutLaisserAvis($id_client, $id_produit) {
        global $pdo;
        
        try {
            // Vérifier si le client a acheté ce produit
            $stmt = $pdo->prepare("
                SELECT c.id as id_commande
                FROM commande c
                JOIN commande_detail cd ON c.id = cd.id_commande
                WHERE c.id_client = ? 
                AND cd.id_produit = ?
                AND c.statut IN ('livrée', 'payée', 'expédiée')
                AND NOT EXISTS (
                    SELECT 1 FROM avis 
                    WHERE id_client = ? 
                    AND id_produit = ? 
                    AND id_commande = c.id
                )
                LIMIT 1
            ");
            
            $stmt->execute([$id_client, $id_produit, $id_client, $id_produit]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur vérification droit avis: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Voter pour un avis (utile/inutile)
     */
    public static function voter($id_avis, $id_client, $type) {
        global $pdo;
        
        try {
            // Supprimer l'ancien vote s'il existe
            $stmt = $pdo->prepare("DELETE FROM avis_vote WHERE id_avis = ? AND id_client = ?");
            $stmt->execute([$id_avis, $id_client]);
            
            // Ajouter le nouveau vote
            $stmt = $pdo->prepare("INSERT INTO avis_vote (id_avis, id_client, type) VALUES (?, ?, ?)");
            return $stmt->execute([$id_avis, $id_client, $type]);
            
        } catch (PDOException $e) {
            error_log("Erreur vote avis: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer tous les avis (pour admin)
     */
    public static function getAll($statut = null) {
        global $pdo;
        
        try {
            $sql = "
                SELECT 
                    a.*,
                    c.prenom,
                    c.nom,
                    c.email,
                    p.marque,
                    p.nom as produit_nom
                FROM avis a
                JOIN client c ON a.id_client = c.id
                JOIN produit p ON a.id_produit = p.id
            ";
            
            if ($statut) {
                $sql .= " WHERE a.statut = ?";
            }
            
            $sql .= " ORDER BY a.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            if ($statut) {
                $stmt->execute([$statut]);
            } else {
                $stmt->execute();
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur get all avis: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer un avis par ID
     */
    public static function getById($id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    a.*,
                    c.prenom,
                    c.nom,
                    c.email,
                    p.marque,
                    p.nom as produit_nom,
                    p.image_principale
                FROM avis a
                JOIN client c ON a.id_client = c.id
                JOIN produit p ON a.id_produit = p.id
                WHERE a.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur get avis by id: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Modifier le statut d'un avis
     */
    public static function updateStatut($id, $statut) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("UPDATE avis SET statut = ? WHERE id = ?");
            return $stmt->execute([$statut, $id]);
            
        } catch (PDOException $e) {
            error_log("Erreur update statut avis: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un avis
     */
    public static function supprimer($id) {
        global $pdo;
        
        try {
            // Supprimer les photos associées
            $photos = self::getPhotos($id);
            foreach ($photos as $photo) {
                $filepath = __DIR__ . '/../public/images/avis/' . $photo['chemin'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
            
            // Supprimer l'avis
            $stmt = $pdo->prepare("DELETE FROM avis WHERE id = ?");
            return $stmt->execute([$id]);
            
        } catch (PDOException $e) {
            error_log("Erreur suppression avis: " . $e->getMessage());
            return false;
        }
    }
}