<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/models/Panier.php';

$base_url = '/diamon_luxe';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['client_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'] ?? $base_url . '/catalogue.php';
    header('Location: ' . $base_url . '/connexion.php');
    exit();
}

$id_produit = $_GET['id'] ?? null;
$quantite = intval($_GET['quantite'] ?? 1);

if (!$id_produit) {
    header('Location: ' . $base_url . '/catalogue.php');
    exit();
}

if (Panier::ajouter($_SESSION['client_id'], $id_produit, $quantite)) {
    $_SESSION['panier_message'] = 'Produit ajouté au panier !';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? $base_url . '/catalogue.php'));
} else {
    $_SESSION['panier_erreur'] = 'Erreur lors de l\'ajout au panier';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? $base_url . '/catalogue.php'));
}
exit();