<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/models/Wishlist.php';

header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['client_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté'
    ]);
    exit();
}

$id_client = $_SESSION['client_id'];
$id_produit = $_GET['id'] ?? null;

if (!$id_produit) {
    echo json_encode([
        'success' => false,
        'message' => 'Produit invalide'
    ]);
    exit();
}

try {
    $in_wishlist = Wishlist::estDansWishlist($id_client, $id_produit);
    
    if ($in_wishlist) {
        // Retirer
        if (Wishlist::retirer($id_client, $id_produit)) {
            echo json_encode([
                'success' => true,
                'in_wishlist' => false,
                'message' => 'Retiré de vos favoris'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors du retrait'
            ]);
        }
    } else {
        // Ajouter
        if (Wishlist::ajouter($id_client, $id_produit)) {
            echo json_encode([
                'success' => true,
                'in_wishlist' => true,
                'message' => 'Ajouté à vos favoris ❤️'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout'
            ]);
        }
    }
    
} catch (Exception $e) {
    error_log("Erreur toggle wishlist: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue'
    ]);
}