<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/Export.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$type = $_POST['type'] ?? null;

if (!$type) {
    header('Location: exports.php');
    exit();
}

switch ($type) {
    case 'stats_ventes':
        $date_debut = $_POST['date_debut'] ?? date('Y-m-01');
        $date_fin = $_POST['date_fin'] ?? date('Y-m-d');
        
        $data = Export::getStatistiquesVentes($date_debut, $date_fin);
        $headers = [
            'Date',
            'Nb Commandes',
            'CA du Jour',
            'Panier Moyen',
            'Nb Clients'
        ];
        $filename = 'stats_ventes_' . $date_debut . '_' . $date_fin . '.csv';
        
        break;
        
    case 'top_produits':
        $limit = intval($_POST['limit'] ?? 20);
        
        $data = Export::getTopProduitsVendus($limit);
        $headers = [
            'ID',
            'Marque',
            'Nom',
            'Prix',
            'Catégorie',
            'Quantité Vendue',
            'CA Généré',
            'Nb Commandes'
        ];
        $filename = 'top_' . $limit . '_produits_' . date('Y-m-d_His') . '.csv';
        
        break;
        
    default:
        header('Location: exports.php');
        exit();
}

// Générer et télécharger le CSV
if (empty($data)) {
    $_SESSION['export_error'] = "Aucune donnée à exporter.";
    header('Location: exports.php');
    exit();
}

Export::genererCSV($data, $headers, $filename);