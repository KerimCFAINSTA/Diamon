<?php
require_once __DIR__ . '/../includes/db.php';

class Analytics {
    
    /**
     * Statistiques globales
     */
    public static function getStatistiquesGlobales() {
        global $pdo;
        
        try {
            // Chiffre d'affaires total
            $stmt = $pdo->query("
                SELECT COALESCE(SUM(montant_total), 0) as ca_total
                FROM commande 
                WHERE statut IN ('payée', 'expédiée', 'livrée')
            ");
            $ca_total = $stmt->fetch()['ca_total'];
            
            // Nombre total de commandes
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM commande");
            $nb_commandes = $stmt->fetch()['total'];
            
            // Nombre de clients
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM client");
            $nb_clients = $stmt->fetch()['total'];
            
            // Panier moyen
            $panier_moyen = $nb_commandes > 0 ? $ca_total / $nb_commandes : 0;
            
            // Commandes du mois en cours
            $stmt = $pdo->query("
                SELECT COUNT(*) as total 
                FROM commande 
                WHERE MONTH(date_commande) = MONTH(CURRENT_DATE())
                AND YEAR(date_commande) = YEAR(CURRENT_DATE())
            ");
            $commandes_mois = $stmt->fetch()['total'];
            
            // CA du mois en cours
            $stmt = $pdo->query("
                SELECT COALESCE(SUM(montant_total), 0) as ca_mois
                FROM commande 
                WHERE MONTH(date_commande) = MONTH(CURRENT_DATE())
                AND YEAR(date_commande) = YEAR(CURRENT_DATE())
                AND statut IN ('payée', 'expédiée', 'livrée')
            ");
            $ca_mois = $stmt->fetch()['ca_mois'];
            
            return [
                'ca_total' => $ca_total,
                'nb_commandes' => $nb_commandes,
                'nb_clients' => $nb_clients,
                'panier_moyen' => $panier_moyen,
                'commandes_mois' => $commandes_mois,
                'ca_mois' => $ca_mois
            ];
            
        } catch (PDOException $e) {
            error_log("Erreur stats globales: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Chiffre d'affaires par mois (12 derniers mois)
     */
    public static function getCAParMois() {
        global $pdo;
        
        try {
            $stmt = $pdo->query("
                SELECT 
                    DATE_FORMAT(date_commande, '%Y-%m') as mois,
                    MONTHNAME(date_commande) as nom_mois,
                    SUM(montant_total) as ca
                FROM commande
                WHERE statut IN ('payée', 'expédiée', 'livrée')
                AND date_commande >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(date_commande, '%Y-%m'), MONTHNAME(date_commande)
                ORDER BY mois ASC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur CA par mois: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Top 5 produits les plus vendus
     */
    public static function getTopProduits($limit = 5) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    p.id,
                    p.marque,
                    p.nom,
                    p.prix,
                    p.image_principale,
                    SUM(cd.quantite) as total_vendu,
                    SUM(cd.quantite * cd.prix_unitaire) as ca_genere
                FROM commande_detail cd
                JOIN produit p ON cd.id_produit = p.id
                JOIN commande c ON cd.id_commande = c.id
                WHERE c.statut IN ('payée', 'expédiée', 'livrée')
                GROUP BY p.id, p.marque, p.nom, p.prix, p.image_principale
                ORDER BY total_vendu DESC
                LIMIT ?
            ");
            
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur top produits: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Répartition des ventes par catégorie
     */
    public static function getVentesParCategorie() {
        global $pdo;
        
        try {
            $stmt = $pdo->query("
                SELECT 
                    cat.nom as categorie,
                    COUNT(DISTINCT c.id) as nb_commandes,
                    SUM(cd.quantite) as nb_produits_vendus,
                    SUM(cd.quantite * cd.prix_unitaire) as ca
                FROM commande_detail cd
                JOIN produit p ON cd.id_produit = p.id
                JOIN categorie cat ON p.id_categorie = cat.id
                JOIN commande c ON cd.id_commande = c.id
                WHERE c.statut IN ('payée', 'expédiée', 'livrée')
                GROUP BY cat.id, cat.nom
                ORDER BY ca DESC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur ventes par catégorie: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Évolution du nombre de clients par mois
     */
    public static function getEvolutionClients() {
        global $pdo;
        
        try {
            $stmt = $pdo->query("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as mois,
                    MONTHNAME(created_at) as nom_mois,
                    COUNT(*) as nb_nouveaux_clients
                FROM client
                WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m'), MONTHNAME(created_at)
                ORDER BY mois ASC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur évolution clients: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Répartition des commandes par statut
     */
    public static function getCommandesParStatut() {
        global $pdo;
        
        try {
            $stmt = $pdo->query("
                SELECT 
                    statut,
                    COUNT(*) as nb_commandes,
                    SUM(montant_total) as ca
                FROM commande
                GROUP BY statut
                ORDER BY nb_commandes DESC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur commandes par statut: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Dernières commandes
     */
    public static function getDernieresCommandes($limit = 5) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    c.id,
                    c.numero_commande,
                    c.date_commande,
                    c.montant_total,
                    c.statut,
                    cl.prenom,
                    cl.nom,
                    cl.email
                FROM commande c
                JOIN client cl ON c.id_client = cl.id
                ORDER BY c.date_commande DESC
                LIMIT ?
            ");
            
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur dernières commandes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Stock faible (produits avec stock <= 5)
     */
    public static function getProduitsStockFaible() {
        global $pdo;
        
        try {
            $stmt = $pdo->query("
                SELECT 
                    p.id,
                    p.marque,
                    p.nom,
                    p.stock,
                    p.image_principale,
                    cat.nom as categorie
                FROM produit p
                JOIN categorie cat ON p.id_categorie = cat.id
                WHERE p.stock <= 5 AND p.disponible = 1
                ORDER BY p.stock ASC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur stock faible: " . $e->getMessage());
            return [];
        }
    }
}