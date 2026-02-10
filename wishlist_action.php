<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/models/Wishlist.php';

$base_url = '/diamon_luxe';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['client_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour gérer votre liste de souhaits.";
    header('Location: ' . $base_url . '/connexion.php?redirect=' . urlencode($_SERVER['HTTP_REFERER'] ?? 'wishlist.php'));
    exit();
}

$id_client = $_SESSION['client_id'];
$action = $_GET['action'] ?? '';
$id_produit = $_GET['id'] ?? null;

// Traiter l'action
switch ($action) {
    case 'ajouter':
        if ($id_produit) {
            if (Wishlist::ajouter($id_client, $id_produit)) {
                $_SESSION['success'] = "Produit ajouté à votre liste de souhaits ! ❤️";
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout à la liste de souhaits.";
            }
        }
        break;
        
    case 'retirer':
        if ($id_produit) {
            if (Wishlist::retirer($id_client, $id_produit)) {
                $_SESSION['success'] = "Produit retiré de votre liste de souhaits.";
            } else {
                $_SESSION['error'] = "Erreur lors du retrait de la liste de souhaits.";
            }
        }
        break;
        
    case 'toggle':
        if ($id_produit) {
            if (Wishlist::estDansWishlist($id_client, $id_produit)) {
                Wishlist::retirer($id_client, $id_produit);
                $_SESSION['success'] = "Produit retiré de votre liste de souhaits.";
            } else {
                Wishlist::ajouter($id_client, $id_produit);
                $_SESSION['success'] = "Produit ajouté à votre liste de souhaits ! ❤️";
            }
        }
        break;
        
    case 'vider':
        if (Wishlist::vider($id_client)) {
            $_SESSION['success'] = "Liste de souhaits vidée.";
        } else {
            $_SESSION['error'] = "Erreur lors du vidage de la liste.";
        }
        break;
        
    case 'vers_panier':
        $nb_ajoutes = Wishlist::deplacerVersPanier($id_client);
        if ($nb_ajoutes > 0) {
            $_SESSION['success'] = "$nb_ajoutes produit(s) ajouté(s) au panier !";
        } else {
            $_SESSION['error'] = "Aucun produit disponible n'a pu être ajouté au panier.";
        }
        header('Location: ' . $base_url . '/views/panier.php');
        exit();
        break;
        
    default:
        $_SESSION['error'] = "Action non valide.";
}

// Redirection
$redirect = $_GET['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? $base_url . '/wishlist.php';
header('Location: ' . $redirect);
exit();