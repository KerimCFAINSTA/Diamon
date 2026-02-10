<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/models/Panier.php';
require_once __DIR__ . '/models/CodePromo.php';

header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['client_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Vous devez être connecté pour utiliser un code promo'
    ]);
    exit();
}

$id_client = $_SESSION['client_id'];
$action = $_POST['action'] ?? '';

if ($action === 'appliquer') {
    
    $code = strtoupper(trim($_POST['code'] ?? ''));
    
    if (empty($code)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Veuillez entrer un code promo'
        ]);
        exit();
    }
    
    try {
        $result = Panier::appliquerCodePromo($id_client, $code);
        
        if ($result['success']) {
            // Recalculer le panier
            $panier = Panier::getContenuComplet($id_client);
            $result['panier'] = $panier;
        }
        
        echo json_encode($result);
        
    } catch (Exception $e) {
        error_log("Erreur application code promo: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Une erreur est survenue : ' . $e->getMessage()
        ]);
    }
    
} elseif ($action === 'retirer') {
    
    try {
        if (Panier::retirerCodePromo($id_client)) {
            // Recalculer le panier
            $panier = Panier::getContenuComplet($id_client);
            
            echo json_encode([
                'success' => true,
                'message' => 'Code promo retiré',
                'panier' => $panier
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Erreur lors du retrait du code promo'
            ]);
        }
    } catch (Exception $e) {
        error_log("Erreur retrait code promo: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Une erreur est survenue : ' . $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Action invalide'
    ]);
}