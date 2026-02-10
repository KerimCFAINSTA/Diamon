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

if ($id_produit) {
    Panier::retirer($_SESSION['client_id'], $id_produit);
}

header('Location: ' . $base_url . '/panier.php');
exit();