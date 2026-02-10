<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/models/Recherche.php';

header('Content-Type: application/json');

// Récupérer les paramètres
$params = [
    'q' => $_GET['q'] ?? '',
    'categorie' => $_GET['categorie'] ?? null,
    'grade' => $_GET['grade'] ?? null,
    'marque' => $_GET['marque'] ?? null,
    'prix_min' => $_GET['prix_min'] ?? null,
    'prix_max' => $_GET['prix_max'] ?? null,
    'en_stock' => $_GET['en_stock'] ?? null,
    'note_min' => $_GET['note_min'] ?? null,
    'sort' => $_GET['sort'] ?? 'nouveautes',
    'limit' => intval($_GET['limit'] ?? 12),
    'offset' => intval($_GET['offset'] ?? 0)
];

// Gérer les filtres multiples (tableaux)
if (isset($_GET['categorie']) && is_array($_GET['categorie'])) {
    $params['categorie'] = array_map('intval', $_GET['categorie']);
}

if (isset($_GET['grade']) && is_array($_GET['grade'])) {
    $params['grade'] = array_map('intval', $_GET['grade']);
}

if (isset($_GET['marque']) && is_array($_GET['marque'])) {
    $params['marque'] = array_map('strval', $_GET['marque']);
}

// Rechercher les produits
$produits = Recherche::rechercherProduits($params);
$total = Recherche::compterResultats($params);

// Sauvegarder la recherche si connecté
if (!empty($params['q']) && isset($_SESSION['client_id'])) {
    Recherche::sauvegarderRecherche($_SESSION['client_id'], $params['q'], $params);
}

// Calculer la pagination
$total_pages = ceil($total / $params['limit']);
$current_page = floor($params['offset'] / $params['limit']) + 1;

echo json_encode([
    'success' => true,
    'produits' => $produits,
    'total' => $total,
    'current_page' => $current_page,
    'total_pages' => $total_pages,
    'params' => $params
]);