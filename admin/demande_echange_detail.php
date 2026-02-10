<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/DemandeEchange.php';

if (!isset($_SESSION['admin'])) {
    die("Accès refusé.");
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: demandes_echange.php');
    exit();
}

$demande = DemandeEchange::getById($id);
if (!$demande) {
    header('Location: demandes_echange.php');
    exit();
}

$base_url = '/diamon_luxe';
$success = false;
$erreur = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveau_statut = $_POST['statut'] ?? '';
    $evaluation = $_POST['evaluation_expert'] ?? null;
    $difference = $_POST['difference_prix'] ?? null;
    $commentaire = $_POST['commentaire_expert'] ?? '';
    
    if (DemandeEchange::updateStatut($id, $nouveau_statut, $evaluation, $difference, $commentaire)) {
    $success = true;
    $demande = DemandeEchange::getById($id);
    
    // 📧 ENVOYER LA NOTIFICATION PAR EMAIL AVEC TOUS LES DÉTAILS
    require_once __DIR__ . '/../includes/email_config.php';
    
    // Préparer les détails de l'échange
    $details_echange = [
        'marque_propose' => $demande['marque_propose'],
        'nom_produit_propose' => $demande['nom_produit_propose'],
        'etat_estime' => $demande['etat_estime'],
        'produit_souhaite_marque' => $demande['produit_souhaite_marque'],
        'produit_souhaite_nom' => $demande['produit_souhaite_nom'],
        'produit_souhaite_prix' => $demande['produit_souhaite_prix']
    ];
    
    $email_envoye = EmailService::notifierStatutEchange(
        $demande['email'],
        $demande['prenom'] . ' ' . $demande['nom'],
        $demande['id'],
        $nouveau_statut,
        $evaluation,
        $difference,
        $commentaire,
        $details_echange // Ajouter les détails
    );
    
    if (!$email_envoye) {
        error_log("Échec envoi email notification échange #" . $demande['id']);
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
    <title>Échange #<?= $demande['id'] ?> | DIAMON Admin</title>
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
            <a href="demandes_echange.php" class="text-sm hover:text-gold">← Retour aux demandes d'échange</a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-12">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-2">
                Demande d'Échange #<?= $demande['id'] ?>
            </h1>
            <p class="text-gray-600">
                <?= date('d/m/Y à H:i', strtotime($demande['created_at'])) ?>
            </p>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-800 px-6 py-4 rounded mb-8">
                ✓ Demande d'échange mise à jour avec succès !
            </div>
        <?php endif; ?>

        <?php if ($erreur): ?>
            <div class="bg-red-100 border border-red-400 text-red-800 px-6 py-4 rounded mb-8">
                <?= htmlspecialchars($erreur) ?>
            </div>
        <?php endif; ?>

        <div class="grid lg:grid-cols-3 gap-8">
            
            <!-- Contenu principal -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Informations Client -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold mb-6">Informations Client</h2>
                    <div class="grid md:grid-cols-2 gap-6">
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

                <!-- Article Souhaité (de notre catalogue) -->
                <div class="bg-gradient-to-br from-gold to-yellow-600 text-white rounded-lg shadow p-6">
                    <div class="flex items-center mb-4">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        <h2 class="text-xl font-bold">Article Souhaité (Notre Catalogue)</h2>
                    </div>
                    
                    <div class="bg-white bg-opacity-10 backdrop-blur-sm p-4 rounded">
                        <div class="flex gap-4">
                            <?php if (!empty($demande['image_principale'])): ?>
                                <img src="<?= $base_url ?>/public/images/produits/<?= $demande['image_principale'] ?>" 
                                     alt="<?= htmlspecialchars($demande['produit_souhaite_nom']) ?>"
                                     class="w-24 h-24 object-cover rounded">
                            <?php endif; ?>
                            <div class="flex-1">
                                <p class="text-xs uppercase tracking-widest font-bold mb-1">
                                    <?= htmlspecialchars($demande['produit_souhaite_marque']) ?>
                                </p>
                                <p class="text-lg font-serif mb-2">
                                    <?= htmlspecialchars($demande['produit_souhaite_nom']) ?>
                                </p>
                                <p class="text-2xl font-bold">
                                    <?= number_format($demande['produit_souhaite_prix'], 0, ',', ' ') ?> €
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Article Proposé par le Client -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center mb-6">
                        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                        </svg>
                        <h2 class="text-xl font-bold">Article Proposé par le Client</h2>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">Marque</p>
                            <p class="font-semibold"><?= htmlspecialchars($demande['marque_propose']) ?></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">Nom du Produit</p>
                            <p class="font-semibold"><?= htmlspecialchars($demande['nom_produit_propose']) ?></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">Catégorie</p>
                            <p><?= htmlspecialchars($demande['categorie_propose']) ?></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">État Estimé</p>
                            <p><span class="bg-black text-white px-2 py-1 text-xs font-bold">Grade <?= htmlspecialchars($demande['etat_estime']) ?></span></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">Valeur Estimée (Client)</p>
                            <p class="text-lg font-serif text-blue-600">
                                <?= $demande['valeur_estimee'] ? number_format($demande['valeur_estimee'], 0, ',', ' ') . ' €' : 'Non spécifié' ?>
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
                            <?= nl2br(htmlspecialchars($demande['description_propose'])) ?>
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

                <!-- Photos de l'article proposé -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold mb-6">Photos de l'Article Proposé</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <?php if (!empty($demande["photo_$i"])): ?>
                                <a href="<?= $base_url ?>/public/images/echanges/<?= $demande["photo_$i"] ?>" target="_blank">
                                    <img src="<?= $base_url ?>/public/images/echanges/<?= $demande["photo_$i"] ?>" 
                                         alt="Photo <?= $i ?>"
                                         class="w-full h-48 object-cover rounded cursor-pointer hover:opacity-75 transition border-2 border-gray-200">
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Calcul de la différence -->
                <?php if ($demande['evaluation_expert'] && $demande['produit_souhaite_prix']): ?>
                    <div class="bg-purple-50 border-l-4 border-purple-500 p-6 rounded-lg">
                        <h3 class="text-lg font-bold mb-4 text-purple-900">Calcul de l'Échange</h3>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-2">Valeur Client</p>
                                <p class="text-2xl font-serif text-blue-600">
                                    <?= number_format($demande['evaluation_expert'], 0, ',', ' ') ?> €
                                </p>
                            </div>
                            <div class="flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-2">Notre Article</p>
                                <p class="text-2xl font-serif text-gold">
                                    <?= number_format($demande['produit_souhaite_prix'], 0, ',', ' ') ?> €
                                </p>
                            </div>
                        </div>
                        
                        <?php 
                        $difference_calculee = $demande['produit_souhaite_prix'] - $demande['evaluation_expert'];
                        ?>
                        
                        <div class="mt-6 pt-6 border-t border-purple-200 text-center">
                            <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-2">Différence à Payer</p>
                            <?php if ($difference_calculee > 0): ?>
                                <p class="text-3xl font-serif text-red-600">
                                    + <?= number_format($difference_calculee, 0, ',', ' ') ?> €
                                </p>
                                <p class="text-sm text-gray-600 mt-2">Le client doit compléter</p>
                            <?php elseif ($difference_calculee < 0): ?>
                                <p class="text-3xl font-serif text-green-600">
                                    <?= number_format($difference_calculee, 0, ',', ' ') ?> €
                                </p>
                                <p class="text-sm text-gray-600 mt-2">Crédit à reverser au client</p>
                            <?php else: ?>
                                <p class="text-3xl font-serif text-gray-600">0 €</p>
                                <p class="text-sm text-gray-600 mt-2">Échange équitable</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Évaluation expert existante -->
                <?php if ($demande['commentaire_expert']): ?>
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg">
                        <p class="text-xs uppercase tracking-widest font-semibold text-blue-800 mb-3">Commentaire Expert</p>
                        <p class="text-sm text-gray-700">
                            <?= nl2br(htmlspecialchars($demande['commentaire_expert'])) ?>
                        </p>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Formulaire de gestion -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6 sticky top-6">
                    <h2 class="text-xl font-bold mb-6">Gérer l'Échange</h2>
                    
                    <form method="post" class="space-y-6">
                        
                        <div>
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Statut</label>
                            <select name="statut" class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                                <option value="en attente" <?= $demande['statut'] == 'en attente' ? 'selected' : '' ?>>En Attente</option>
                                <option value="en cours d'évaluation" <?= $demande['statut'] == "en cours d'évaluation" ? 'selected' : '' ?>>En Cours d'Évaluation</option>
                                <option value="acceptée" <?= $demande['statut'] == 'acceptée' ? 'selected' : '' ?>>Acceptée</option>
                                <option value="refusée" <?= $demande['statut'] == 'refusée' ? 'selected' : '' ?>>Refusée</option>
                                <option value="échange effectué" <?= $demande['statut'] == 'échange effectué' ? 'selected' : '' ?>>Échange Effectué</option>
                            </select>
                        </div>

                        <div class="bg-blue-50 p-4 rounded">
                            <p class="text-xs uppercase tracking-widest font-semibold text-blue-800 mb-3">Évaluation de l'Article Client</p>
                            
                            <div class="mb-4">
                                <label class="block text-xs font-semibold mb-2">Valeur Expert (€)</label>
                                <input type="number" 
                                       name="evaluation_expert" 
                                       value="<?= htmlspecialchars($demande['evaluation_expert'] ?? '') ?>"
                                       step="0.01"
                                       min="0"
                                       placeholder="Ex: 7500"
                                       class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                                <p class="text-xs text-gray-500 mt-1">Estimation de l'article proposé par le client</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold mb-2">Différence (€)</label>
                                <input type="number" 
                                       name="difference_prix" 
                                       value="<?= htmlspecialchars($demande['difference_prix'] ?? '') ?>"
                                       step="0.01"
                                       placeholder="Ex: 1500"
                                       class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                                <p class="text-xs text-gray-500 mt-1">Montant que le client doit compléter (si positif) ou créditer (si négatif)</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Commentaire Expert</label>
                            <textarea name="commentaire_expert" 
                                      rows="8"
                                      placeholder="Évaluation détaillée de l'article proposé, condition, authenticité, recommandations..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none"><?= htmlspecialchars($demande['commentaire_expert'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" 
                                class="w-full bg-black text-white py-4 text-xs font-bold uppercase tracking-widest rounded hover:bg-gold transition">
                            Mettre à Jour l'Échange
                        </button>

                    </form>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <p class="text-xs text-gray-500">
                            Demande créée le<br>
                            <strong><?= date('d/m/Y à H:i', strtotime($demande['created_at'])) ?></strong>
                        </p>
                    </div>

                    <!-- Résumé rapide -->
                    <div class="mt-6 pt-6 border-t border-gray-200 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Article souhaité:</span>
                            <span class="font-semibold text-gold">
                                <?= number_format($demande['produit_souhaite_prix'], 0, ',', ' ') ?> €
                            </span>
                        </div>
                        <?php if ($demande['valeur_estimee']): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Estimé client:</span>
                            <span class="font-semibold text-blue-600">
                                <?= number_format($demande['valeur_estimee'], 0, ',', ' ') ?> €
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

    </div>

</body>
</html>