<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/Produit.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'] ?? null;

if ($id && Produit::supprimer($id)) {
    header('Location: produits.php?deleted=1');
} else {
    header('Location: produits.php?error=1');
}
exit();