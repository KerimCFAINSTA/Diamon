<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/DemandeVente.php';

if (!isset($_SESSION['admin'])) {
    die("Accès refusé.");
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: demandes_vente.php');
    exit();
}

$demande = DemandeVente::getById($id);
if (!$demande) {
    header('Location: demandes_vente.php');
    exit();
}

$base_url = '/diamon_luxe';
$success = false;
$erreur = '';

// Traitement du formulaire
// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveau_statut = $_POST['statut'] ?? '';
    $estimation = $_POST['estimation_expert'] ?? null;
    $commentaire = $_POST['commentaire_expert'] ?? '';
    
    if (DemandeVente::updateStatut($id, $nouveau_statut, $estimation, $commentaire)) {
    $success = true;
    $demande = DemandeVente::getById($id); // Recharger
    
    // 📧 ENVOYER LA NOTIFICATION PAR EMAIL AVEC TOUS LES DÉTAILS
    require_once __DIR__ . '/../includes/email_config.php';
    
    // Préparer les détails du produit
    $details_produit = [
        'marque' => $demande['marque'],
        'nom_produit' => $demande['nom_produit'],
        'categorie' => $demande['categorie'],
        'etat_estime' => $demande['etat_estime']
    ];
    
    $email_envoye = EmailService::notifierStatutVente(
        $demande['email'],
        $demande['prenom'] . ' ' . $demande['nom'],
        $demande['id'],
        $nouveau_statut,
        $estimation,
        $commentaire,
        $details_produit // Ajouter les détails
    );
    
    if (!$email_envoye) {
        error_log("Échec envoi email notification vente #" . $demande['id']);
    }
} else {
    $erreur = "Erreur lors de la mise à jour.";
}
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande #<?= $demande['id'] ?> | DIAMON Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .bg-gold { background-color: #C5A059; }
        .text-gold { color: #C5A059; }
    </style>
</head>
<body class="bg-gray-100">
    
    <nav class="bg-black text-white p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-serif">DIAMON - Admin</h1>
            <a href="demandes_vente.php" class="text-sm hover:text-gold">← Retour aux demandes</a>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-6 py-12">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-2">
                Demande de Vente #<?= $demande['id'] ?>
            </h1>
            <p class="text-gray-600">
                <?= htmlspecialchars($demande['marque']) ?> - <?= htmlspecialchars($demande['nom_produit']) ?>
            </p>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-800 px-6 py-4 rounded mb-8">
                ✓ Demande mise à jour avec succès !
            </div>
        <?php endif; ?>

        <?php if ($erreur): ?>
            <div class="bg-red-100 border border-red-400 text-red-800 px-6 py-4 rounded mb-8">
                <?= htmlspecialchars($erreur) ?>
            </div>
        <?php endif; ?>

        <div class="grid lg:grid-cols-3 gap-8">
            
            <!-- Informations -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Détails produit -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold mb-6">Détails de l'Article</h2>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">Marque</p>
                            <p class="font-semibold"><?= htmlspecialchars($demande['marque']) ?></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">Nom du Produit</p>
                            <p class="font-semibold"><?= htmlspecialchars($demande['nom_produit']) ?></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">Catégorie</p>
                            <p><?= htmlspecialchars($demande['categorie']) ?></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">État Estimé</p>
                            <p><span class="bg-black text-white px-2 py-1 text-xs font-bold">Grade <?= htmlspecialchars($demande['etat_estime']) ?></span></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">Prix Souhaité</p>
                            <p class="text-lg font-serif text-gold">
                                <?= $demande['prix_souhaite'] ? number_format($demande['prix_souhaite'], 0, ',', ' ') . ' €' : 'Non spécifié' ?>
                            </p>
                        </div>
                        <?php if ($demande['annee_achat']): ?>
                        <div>
                            <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">Année d'Achat</p>
                            <p><?= htmlspecialchars($demande['annee_achat']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-6">
                        <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-2">Description</p>
                        <p class="text-sm text-gray-700 bg-gray-50 p-4 rounded">
                            <?= nl2br(htmlspecialchars($demande['description'])) ?>
                        </p>
                    </div>

                    <div class="mt-6 flex gap-6 text-sm">
                        <span class="<?= $demande['avec_boite'] ? 'text-green-600 font-semibold' : 'text-gray-400' ?>">
                            <?= $demande['avec_boite'] ? '✓' : '✗' ?> Boîte d'origine
                        </span>
                        <span class="<?= $demande['avec_certificat'] ? 'text-green-600 font-semibold' : 'text-gray-400' ?>">
                            <?= $demande['avec_certificat'] ? '✓' : '✗' ?> Certificat
                        </span>
                        <span class="<?= $demande['avec_accessoires'] ? 'text-green-600 font-semibold' : 'text-gray-400' ?>">
                            <?= $demande['avec_accessoires'] ? '✓' : '✗' ?> Accessoires
                        </span>
                    </div>
                </div>

                <!-- Photos -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold mb-6">Photos de l'Article</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <?php if (!empty($demande["photo_$i"])): ?>
                                <a href="<?= $base_url ?>/public/images/ventes/<?= $demande["photo_$i"] ?>" target="_blank">
                                    <img src="<?= $base_url ?>/public/images/ventes/<?= $demande["photo_$i"] ?>" 
                                         alt="Photo <?= $i ?>"
                                         class="w-full h-48 object-cover rounded cursor-pointer hover:opacity-75 transition border-2 border-gray-200">
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Informations client -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold mb-6">Informations Client</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">Nom</p>
                            <p class="font-semibold"><?= htmlspecialchars($demande['prenom'] . ' ' . $demande['nom']) ?></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">Email</p>
                            <p><a href="mailto:<?= htmlspecialchars($demande['email']) ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($demande['email']) ?></a></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">Téléphone</p>
                            <p><a href="tel:<?= htmlspecialchars($demande['telephone']) ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($demande['telephone']) ?></a></p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Formulaire de gestion -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6 sticky top-6">
                    <h2 class="text-xl font-bold mb-6">Gérer la Demande</h2>
                    
                    <form method="post" class="space-y-6">
                        
                        <div>
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Statut</label>
                            <select name="statut" class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                                <option value="en attente" <?= $demande['statut'] == 'en attente' ? 'selected' : '' ?>>En Attente</option>
                                <option value="en cours d'évaluation" <?= $demande['statut'] == "en cours d'évaluation" ? 'selected' : '' ?>>En Cours d'Évaluation</option>
                                <option value="acceptée" <?= $demande['statut'] == 'acceptée' ? 'selected' : '' ?>>Acceptée</option>
                                <option value="refusée" <?= $demande['statut'] == 'refusée' ? 'selected' : '' ?>>Refusée</option>
                                <option value="vendue" <?= $demande['statut'] == 'vendue' ? 'selected' : '' ?>>Vendue</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Estimation Expert (€)</label>
                            <input type="number" 
                                   name="estimation_expert" 
                                   value="<?= htmlspecialchars($demande['estimation_expert'] ?? '') ?>"
                                   step="0.01"
                                   min="0"
                                   placeholder="8500"
                                   class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Commentaire Expert</label>
                            <textarea name="commentaire_expert" 
                                      rows="6"
                                      placeholder="Évaluation détaillée de l'article..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none"><?= htmlspecialchars($demande['commentaire_expert'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" 
                                class="w-full bg-black text-white py-4 text-xs font-bold uppercase tracking-widest rounded hover:bg-gold transition">
                            Mettre à Jour
                        </button>

                    </form>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <p class="text-xs text-gray-500">
                            Demande créée le<br>
                            <strong><?= date('d/m/Y à H:i', strtotime($demande['created_at'])) ?></strong>
                        </p>
                    </div>
                </div>
            </div>

        </div>

    </div>

</body>
</html>