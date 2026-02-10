<?php
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Requête trop courte']);
    exit();
}

try {
    $searchTerm = '%' . $query . '%';
    
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.marque,
            p.nom,
            p.prix,
            p.image_principale
        FROM produit p
        WHERE p.disponible = 1
        AND (p.nom LIKE ? OR p.marque LIKE ? OR p.description LIKE ?)
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'produits' => $produits
    ]);
    
} catch (PDOException $e) {
    error_log("Erreur suggestions: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}