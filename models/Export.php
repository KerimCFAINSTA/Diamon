<?php
require_once __DIR__ . '/../includes/db.php';

class Export {
    
    /**
     * Exporter les produits en CSV
     */
    public static function exporterProduits($filtres = []) {
        global $pdo;
        
        try {
            $sql = "
                SELECT 
                    p.id,
                    p.marque,
                    p.nom,
                    p.description,
                    p.prix,
                    p.stock,
                    p.disponible,
                    c.nom as categorie,
                    g.code as grade_code,
                    g.nom as grade_nom,
                    p.created_at
                FROM produit p
                LEFT JOIN categorie c ON p.id_categorie = c.id
                LEFT JOIN grade g ON p.id_grade = g.id
                WHERE 1=1
            ";
            
            // Appliquer les filtres
            $params = [];
            
            if (!empty($filtres['categorie'])) {
                $sql .= " AND p.id_categorie = ?";
                $params[] = $filtres['categorie'];
            }
            
            if (isset($filtres['disponible'])) {
                $sql .= " AND p.disponible = ?";
                $params[] = $filtres['disponible'];
            }
            
            if (!empty($filtres['date_debut'])) {
                $sql .= " AND DATE(p.created_at) >= ?";
                $params[] = $filtres['date_debut'];
            }
            
            if (!empty($filtres['date_fin'])) {
                $sql .= " AND DATE(p.created_at) <= ?";
                $params[] = $filtres['date_fin'];
            }
            
            $sql .= " ORDER BY p.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur export produits: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Exporter les commandes en CSV
     */
    public static function exporterCommandes($filtres = []) {
        global $pdo;
        
        try {
            $sql = "
                SELECT 
                    c.id,
                    c.numero_commande,
                    c.date_commande,
                    c.montant_total,
                    c.statut,
                    cl.prenom,
                    cl.nom,
                    cl.email,
                    cl.telephone,
                    c.adresse_livraison,
                    (SELECT COUNT(*) FROM commande_detail WHERE id_commande = c.id) as nb_articles
                FROM commande c
                JOIN client cl ON c.id_client = cl.id
                WHERE 1=1
            ";
            
            $params = [];
            
            if (!empty($filtres['statut'])) {
                $sql .= " AND c.statut = ?";
                $params[] = $filtres['statut'];
            }
            
            if (!empty($filtres['date_debut'])) {
                $sql .= " AND DATE(c.date_commande) >= ?";
                $params[] = $filtres['date_debut'];
            }
            
            if (!empty($filtres['date_fin'])) {
                $sql .= " AND DATE(c.date_commande) <= ?";
                $params[] = $filtres['date_fin'];
            }
            
            $sql .= " ORDER BY c.date_commande DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur export commandes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Exporter les clients en CSV
     */
    public static function exporterClients($filtres = []) {
        global $pdo;
        
        try {
            $sql = "
                SELECT 
                    c.id,
                    c.prenom,
                    c.nom,
                    c.email,
                    c.telephone,
                    c.adresse,
                    c.ville,
                    c.code_postal,
                    c.created_at,
                    (SELECT COUNT(*) FROM commande WHERE id_client = c.id) as nb_commandes,
                    (SELECT COALESCE(SUM(montant_total), 0) FROM commande WHERE id_client = c.id) as total_depense
                FROM client c
                WHERE 1=1
            ";
            
            $params = [];
            
            if (!empty($filtres['date_debut'])) {
                $sql .= " AND DATE(c.created_at) >= ?";
                $params[] = $filtres['date_debut'];
            }
            
            if (!empty($filtres['date_fin'])) {
                $sql .= " AND DATE(c.created_at) <= ?";
                $params[] = $filtres['date_fin'];
            }
            
            $sql .= " ORDER BY c.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur export clients: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Exporter les avis en CSV
     */
    public static function exporterAvis($filtres = []) {
        global $pdo;
        
        try {
            $sql = "
                SELECT 
                    a.id,
                    p.marque,
                    p.nom as produit,
                    cl.prenom,
                    cl.nom,
                    cl.email,
                    a.note,
                    a.titre,
                    a.commentaire,
                    a.recommande,
                    a.statut,
                    a.verifie,
                    a.created_at
                FROM avis a
                JOIN produit p ON a.id_produit = p.id
                JOIN client cl ON a.id_client = cl.id
                WHERE 1=1
            ";
            
            $params = [];
            
            if (!empty($filtres['statut'])) {
                $sql .= " AND a.statut = ?";
                $params[] = $filtres['statut'];
            }
            
            if (!empty($filtres['note_min'])) {
                $sql .= " AND a.note >= ?";
                $params[] = $filtres['note_min'];
            }
            
            if (!empty($filtres['date_debut'])) {
                $sql .= " AND DATE(a.created_at) >= ?";
                $params[] = $filtres['date_debut'];
            }
            
            if (!empty($filtres['date_fin'])) {
                $sql .= " AND DATE(a.created_at) <= ?";
                $params[] = $filtres['date_fin'];
            }
            
            $sql .= " ORDER BY a.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur export avis: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Générer un fichier CSV
     */
    public static function genererCSV($data, $headers, $filename) {
        // Définir les en-têtes HTTP pour le téléchargement
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Créer le flux de sortie
        $output = fopen('php://output', 'w');
        
        // Ajouter le BOM UTF-8 pour Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Écrire les en-têtes
        fputcsv($output, $headers, ';');
        
        // Écrire les données
        foreach ($data as $row) {
            $row_data = [];
            foreach ($headers as $header) {
                // Trouver la clé correspondante (insensible à la casse)
                $key = null;
                foreach (array_keys($row) as $k) {
                    if (strtolower($k) === strtolower($header)) {
                        $key = $k;
                        break;
                    }
                }
                $row_data[] = $key ? $row[$key] : '';
            }
            fputcsv($output, $row_data, ';');
        }
        
        fclose($output);
        exit();
    }
    /**
     * Exporter les détails d'une commande (avec lignes de commande)
     */
    public static function exporterDetailCommande($id_commande) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    c.numero_commande,
                    c.date_commande,
                    cl.prenom,
                    cl.nom,
                    cl.email,
                    p.marque,
                    p.nom as produit,
                    cd.quantite,
                    cd.prix_unitaire,
                    (cd.quantite * cd.prix_unitaire) as sous_total
                FROM commande_detail cd
                JOIN commande c ON cd.id_commande = c.id
                JOIN client cl ON c.id_client = cl.id
                JOIN produit p ON cd.id_produit = p.id
                WHERE c.id = ?
            ");
            
            $stmt->execute([$id_commande]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur export détail commande: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Statistiques pour export Excel avancé
     */
    public static function getStatistiquesVentes($date_debut, $date_fin) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    DATE(c.date_commande) as date,
                    COUNT(DISTINCT c.id) as nb_commandes,
                    SUM(c.montant_total) as ca_jour,
                    AVG(c.montant_total) as panier_moyen,
                    COUNT(DISTINCT c.id_client) as nb_clients
                FROM commande c
                WHERE DATE(c.date_commande) BETWEEN ? AND ?
                AND c.statut IN ('payée', 'expédiée', 'livrée')
                GROUP BY DATE(c.date_commande)
                ORDER BY date ASC
            ");
            
            $stmt->execute([$date_debut, $date_fin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur stats ventes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Top produits vendus
     */
    public static function getTopProduitsVendus($limit = 20) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    p.id,
                    p.marque,
                    p.nom,
                    p.prix,
                    c.nom as categorie,
                    SUM(cd.quantite) as quantite_vendue,
                    SUM(cd.quantite * cd.prix_unitaire) as ca_genere,
                    COUNT(DISTINCT cd.id_commande) as nb_commandes
                FROM commande_detail cd
                JOIN produit p ON cd.id_produit = p.id
                JOIN categorie c ON p.id_categorie = c.id
                JOIN commande com ON cd.id_commande = com.id
                WHERE com.statut IN ('payée', 'expédiée', 'livrée')
                GROUP BY p.id, p.marque, p.nom, p.prix, c.nom
                ORDER BY quantite_vendue DESC
                LIMIT ?
            ");
            
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur top produits: " . $e->getMessage());
            return [];
        }
    }
}