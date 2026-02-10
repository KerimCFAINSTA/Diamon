<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'] ?? null;
$produit_id = $_GET['produit'] ?? null;

if ($id && $produit_id) {
    try {
        // Récupérer le chemin de l'image
        $stmt = $pdo->prepare("SELECT chemin FROM produit_image WHERE id = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetch();
        
        if ($image) {
            // Supprimer le fichier
            $filepath = __DIR__ . '/../public/images/produits/' . $image['chemin'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            
            // Supprimer de la BDD
            $stmt = $pdo->prepare("DELETE FROM produit_image WHERE id = ?");
            $stmt->execute([$id]);
        }
        
        header("Location: produit_modifier.php?id=$produit_id&image_deleted=1");
    } catch (PDOException $e) {
        header("Location: produit_modifier.php?id=$produit_id&error=1");
    }
} else {
    header('Location: produits.php');
}
exit();