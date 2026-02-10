<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/Avis.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: avis.php');
    exit();
}

if (Avis::supprimer($id)) {
    header('Location: avis.php?deleted=1');
} else {
    header('Location: avis.php?error=1');
}
exit();