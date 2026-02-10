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

// Récupérer les filtres
$filtres = [];

switch ($type) {
    case 'produits':
        if (!empty($_POST['categorie'])) {
            $filtres['categorie'] = $_POST['categorie'];
        }
        if (isset($_POST['disponible']) && $_POST['disponible'] !== '') {
            $filtres['disponible'] = $_POST['disponible'];
        }
        if (!empty($_POST['date_debut'])) {
            $filtres['date_debut'] = $_POST['date_debut'];
        }
        if (!empty($_POST['date_fin'])) {
            $filtres['date_fin'] = $_POST['date_fin'];
        }
        
        $data = Export::exporterProduits($filtres);
        $headers = [
            'ID',
            'Marque',
            'Nom',
            'Description',
            'Prix',
            'Stock',
            'Disponible',
            'Catégorie',
            'Grade Code',
            'Grade Nom',
            'Date Création'
        ];
        $filename = 'produits_' . date('Y-m-d_His') . '.csv';
        
        break;
        
    case 'commandes':
        if (!empty($_POST['statut'])) {
            $filtres['statut'] = $_POST['statut'];
        }
        if (!empty($_POST['date_debut'])) {
            $filtres['date_debut'] = $_POST['date_debut'];
        }
        if (!empty($_POST['date_fin'])) {
            $filtres['date_fin'] = $_POST['date_fin'];
        }
        
        $data = Export::exporterCommandes($filtres);
        $headers = [
            'ID',
            'Numéro Commande',
            'Date Commande',
            'Montant Total',
            'Statut',
            'Prénom Client',
            'Nom Client',
            'Email',
            'Téléphone',
            'Adresse Livraison',
            'Nb Articles'
        ];
        $filename = 'commandes_' . date('Y-m-d_His') . '.csv';
        
        break;
        
    case 'clients':
        if (!empty($_POST['date_debut'])) {
            $filtres['date_debut'] = $_POST['date_debut'];
        }
        if (!empty($_POST['date_fin'])) {
            $filtres['date_fin'] = $_POST['date_fin'];
        }
        
        $data = Export::exporterClients($filtres);
        $headers = [
            'ID',
            'Prénom',
            'Nom',
            'Email',
            'Téléphone',
            'Adresse',
            'Ville',
            'Code Postal',
            'Date Inscription',
            'Nb Commandes',
            'Total Dépensé'
        ];
        $filename = 'clients_' . date('Y-m-d_His') . '.csv';
        
        break;
        
    case 'avis':
        if (!empty($_POST['statut'])) {
            $filtres['statut'] = $_POST['statut'];
        }
        if (!empty($_POST['note_min'])) {
            $filtres['note_min'] = $_POST['note_min'];
        }
        if (!empty($_POST['date_debut'])) {
            $filtres['date_debut'] = $_POST['date_debut'];
        }
        if (!empty($_POST['date_fin'])) {
            $filtres['date_fin'] = $_POST['date_fin'];
        }
        
        $data = Export::exporterAvis($filtres);
        $headers = [
            'ID',
            'Marque',
            'Produit',
            'Prénom Client',
            'Nom Client',
            'Email',
            'Note',
            'Titre',
            'Commentaire',
            'Recommande',
            'Statut',
            'Vérifié',
            'Date Publication'
        ];
        $filename = 'avis_' . date('Y-m-d_His') . '.csv';
        
        break;
        
    default:
        header('Location: exports.php');
        exit();
}

// Générer et télécharger le CSV
if (empty($data)) {
    $_SESSION['export_error'] = "Aucune donnée à exporter avec les filtres sélectionnés.";
    header('Location: exports.php');
    exit();
}

Export::genererCSV($data, $headers, $filename);