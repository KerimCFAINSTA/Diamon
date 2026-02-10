<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/models/Panier.php';

$base_url = '/diamon_luxe';

if (!isset($_SESSION['client_id'])) {
    header('Location: ' . $base_url . '/connexion.php');
    exit();
}

$id_produit = $_GET['id'] ?? null;
$action = $_GET['action'] ?? '';

if (!$id_produit) {
    header('Location: ' . $base_url . '/panier.php');
    exit();
}

// Récupérer la quantité actuelle
$articles = Panier::getArticles($_SESSION['client_id']);
$quantite_actuelle = 0;

foreach ($articles as $article) {
    if ($article['id_produit'] == $id_produit) {
        $quantite_actuelle = $article['quantite'];
        break;
    }
}

// Modifier la quantité
if ($action === 'augmenter') {
    Panier::modifierQuantite($_SESSION['client_id'], $id_produit, $quantite_actuelle + 1);
} elseif ($action === 'diminuer') {
    if ($quantite_actuelle > 1) {
        Panier::modifierQuantite($_SESSION['client_id'], $id_produit, $quantite_actuelle - 1);
    } else {
        Panier::retirer($_SESSION['client_id'], $id_produit);
    }
}

header('Location: ' . $base_url . '/panier.php');
exit();