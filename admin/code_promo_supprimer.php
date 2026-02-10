<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/CodePromo.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'] ?? null;

if ($id && CodePromo::supprimer($id)) {
    header('Location: codes_promo.php?deleted=1');
} else {
    header('Location: codes_promo.php?error=1');
}
exit();