<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/Avis.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$id || !$action) {
    header('Location: avis.php');
    exit();
}

$statut = null;
$redirect_param = '';

switch ($action) {
    case 'approuver':
        $statut = 'approuve';
        $redirect_param = 'approved=1';
        break;
    case 'rejeter':
        $statut = 'rejete';
        $redirect_param = 'rejected=1';
        break;
    default:
        header('Location: avis.php');
        exit();
}

if (Avis::updateStatut($id, $statut)) {
    header('Location: avis.php?' . $redirect_param);
} else {
    header('Location: avis.php?error=1');
}
exit();