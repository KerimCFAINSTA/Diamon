<?php
require_once __DIR__ . '/../includes/db.php';

class Recherche {
    
    /**
     * Recherche avancée de produits avec filtres
     */
    public static function rechercherProduits($params = []) {
        global $pdo;
        
        try {
            $sql = "
                SELECT DISTINCT
                    p.id,
                    p.marque,
                    p.nom,
                    p.description,
                    p.prix,
                    p.stock,
                    p.disponible,
                    p.image_principale,
                    c.nom as categorie_nom,
                    c.slug as categorie_slug,
                    g.code as grade_code,
                    g.nom as grade_nom,
                    (SELECT AVG(note) FROM avis WHERE id_produit = p.id AND statut = 'approuve') as note_moyenne,
                    (SELECT COUNT(*) FROM avis WHERE id_produit = p.id AND statut = 'approuve') as nb_avis
                FROM produit p
                LEFT JOIN categorie c ON p.id_categorie = c.id
                LEFT JOIN grade g ON p.id_grade = g.id
                WHERE p.disponible = 1
            ";
            
            $conditions = [];
            $bindings = [];
            
            // Recherche par mot-clé
            if (!empty($params['q'])) {
                $conditions[] = "(p.nom LIKE ? OR p.marque LIKE ? OR p.description LIKE ?)";
                $searchTerm = '%' . $params['q'] . '%';
                $bindings[] = $searchTerm;
                $bindings[] = $searchTerm;
                $bindings[] = $searchTerm;
            }
            
            // Filtre par catégorie
            if (!empty($params['categorie'])) {
                if (is_array($params['categorie'])) {
                    $placeholders = str_repeat('?,', count($params['categorie']) - 1) . '?';
                    $conditions[] = "p.id_categorie IN ($placeholders)";
                    $bindings = array_merge($bindings, $params['categorie']);
                } else {
                    $conditions[] = "p.id_categorie = ?";
                    $bindings[] = $params['categorie'];
                }
            }
            
            // Filtre par grade
            if (!empty($params['grade'])) {
                if (is_array($params['grade'])) {
                    $placeholders = str_repeat('?,', count($params['grade']) - 1) . '?';
                    $conditions[] = "p.id_grade IN ($placeholders)";
                    $bindings = array_merge($bindings, $params['grade']);
                } else {
                    $conditions[] = "p.id_grade = ?";
                    $bindings[] = $params['grade'];
                }
            }
            
            // Filtre par marque
            if (!empty($params['marque'])) {
                if (is_array($params['marque'])) {
                    $placeholders = str_repeat('?,', count($params['marque']) - 1) . '?';
                    $conditions[] = "p.marque IN ($placeholders)";
                    $bindings = array_merge($bindings, $params['marque']);
                } else {
                    $conditions[] = "p.marque = ?";
                    $bindings[] = $params['marque'];
                }
            }
            
            // Filtre par prix
            if (!empty($params['prix_min'])) {
                $conditions[] = "p.prix >= ?";
                $bindings[] = floatval($params['prix_min']);
            }
            
            if (!empty($params['prix_max'])) {
                $conditions[] = "p.prix <= ?";
                $bindings[] = floatval($params['prix_max']);
            }
            
            // Filtre par disponibilité en stock
            if (isset($params['en_stock']) && $params['en_stock'] == '1') {
                $conditions[] = "p.stock > 0";
            }
            
            // Filtre par note minimum
            if (!empty($params['note_min'])) {
                $conditions[] = "(SELECT AVG(note) FROM avis WHERE id_produit = p.id AND statut = 'approuve') >= ?";
                $bindings[] = floatval($params['note_min']);
            }
            
            // Ajouter les conditions à la requête
            if (!empty($conditions)) {
                $sql .= " AND " . implode(" AND ", $conditions);
            }
            
            // Tri
            $order = "p.created_at DESC"; // Par défaut : nouveautés
            
            if (!empty($params['sort'])) {
                switch ($params['sort']) {
                    case 'prix_asc':
                        $order = "p.prix ASC";
                        break;
                    case 'prix_desc':
                        $order = "p.prix DESC";
                        break;
                    case 'nom_asc':
                        $order = "p.nom ASC";
                        break;
                    case 'nom_desc':
                        $order = "p.nom DESC";
                        break;
                    case 'populaire':
                        $order = "nb_avis DESC, note_moyenne DESC";
                        break;
                    case 'note':
                        $order = "note_moyenne DESC";
                        break;
                    case 'nouveautes':
                    default:
                        $order = "p.created_at DESC";
                        break;
                }
            }
            
            $sql .= " ORDER BY " . $order;
            
            // Pagination
            $limit = isset($params['limit']) ? intval($params['limit']) : 12;
            $offset = isset($params['offset']) ? intval($params['offset']) : 0;
            
            $sql .= " LIMIT ? OFFSET ?";
            $bindings[] = $limit;
            $bindings[] = $offset;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($bindings);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur recherche produits: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Compter le nombre total de résultats (pour pagination)
     */
    public static function compterResultats($params = []) {
        global $pdo;
        
        try {
            $sql = "
                SELECT COUNT(DISTINCT p.id) as total
                FROM produit p
                LEFT JOIN categorie c ON p.id_categorie = c.id
                LEFT JOIN grade g ON p.id_grade = g.id
                WHERE p.disponible = 1
            ";
            
            $conditions = [];
            $bindings = [];
            
            // Même logique de filtrage que rechercherProduits
            if (!empty($params['q'])) {
                $conditions[] = "(p.nom LIKE ? OR p.marque LIKE ? OR p.description LIKE ?)";
                $searchTerm = '%' . $params['q'] . '%';
                $bindings[] = $searchTerm;
                $bindings[] = $searchTerm;
                $bindings[] = $searchTerm;
            }
            
            if (!empty($params['categorie'])) {
                if (is_array($params['categorie'])) {
                    $placeholders = str_repeat('?,', count($params['categorie']) - 1) . '?';
                    $conditions[] = "p.id_categorie IN ($placeholders)";
                    $bindings = array_merge($bindings, $params['categorie']);
                } else {
                    $conditions[] = "p.id_categorie = ?";
                    $bindings[] = $params['categorie'];
                }
            }
            
            if (!empty($params['grade'])) {
                if (is_array($params['grade'])) {
                    $placeholders = str_repeat('?,', count($params['grade']) - 1) . '?';
                    $conditions[] = "p.id_grade IN ($placeholders)";
                    $bindings = array_merge($bindings, $params['grade']);
                } else {
                    $conditions[] = "p.id_grade = ?";
                    $bindings[] = $params['grade'];
                }
            }
            
            if (!empty($params['marque'])) {
                if (is_array($params['marque'])) {
                    $placeholders = str_repeat('?,', count($params['marque']) - 1) . '?';
                    $conditions[] = "p.marque IN ($placeholders)";
                    $bindings = array_merge($bindings, $params['marque']);
                } else {
                    $conditions[] = "p.marque = ?";
                    $bindings[] = $params['marque'];
                }
            }
            
            if (!empty($params['prix_min'])) {
                $conditions[] = "p.prix >= ?";
                $bindings[] = floatval($params['prix_min']);
            }
            
            if (!empty($params['prix_max'])) {
                $conditions[] = "p.prix <= ?";
                $bindings[] = floatval($params['prix_max']);
            }
            
            if (isset($params['en_stock']) && $params['en_stock'] == '1') {
                $conditions[] = "p.stock > 0";
            }
            
            if (!empty($params['note_min'])) {
                $conditions[] = "(SELECT AVG(note) FROM avis WHERE id_produit = p.id AND statut = 'approuve') >= ?";
                $bindings[] = floatval($params['note_min']);
            }
            
            if (!empty($conditions)) {
                $sql .= " AND " . implode(" AND ", $conditions);
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($bindings);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return intval($result['total']);
            
        } catch (PDOException $e) {
            error_log("Erreur comptage résultats: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtenir toutes les marques disponibles
     */
    public static function getMarquesDisponibles() {
        global $pdo;
        
        try {
            $stmt = $pdo->query("
                SELECT DISTINCT marque, COUNT(*) as nb_produits
                FROM produit
                WHERE disponible = 1
                GROUP BY marque
                ORDER BY marque ASC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur get marques: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir les plages de prix (min et max)
     */
    public static function getPlagePrix() {
        global $pdo;
        
        try {
            $stmt = $pdo->query("
                SELECT 
                    MIN(prix) as prix_min,
                    MAX(prix) as prix_max
                FROM produit
                WHERE disponible = 1
            ");
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur get plage prix: " . $e->getMessage());
            return ['prix_min' => 0, 'prix_max' => 0];
        }
    }
    
    /**
     * Sauvegarder une recherche (historique)
     */
    public static function sauvegarderRecherche($id_client, $query, $filtres) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO recherche_historique (id_client, query, filtres)
                VALUES (?, ?, ?)
            ");
            
            return $stmt->execute([
                $id_client,
                $query,
                json_encode($filtres)
            ]);
            
        } catch (PDOException $e) {
            error_log("Erreur sauvegarde recherche: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir les recherches récentes d'un client
     */
    public static function getRecherchesRecentes($id_client, $limit = 5) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT DISTINCT query
                FROM recherche_historique
                WHERE id_client = ? AND query != ''
                ORDER BY created_at DESC
                LIMIT ?
            ");
            
            $stmt->execute([$id_client, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur recherches récentes: " . $e->getMessage());
            return [];
        }
    }
}