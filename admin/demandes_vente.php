<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/DemandeVente.php';

// Protection admin simple (à améliorer avec un vrai système d'auth admin)
if (!isset($_SESSION['admin'])) {
    // Pour tester temporairement : $_SESSION['admin'] = true;
    die("Accès refusé. Veuillez vous connecter en tant qu'administrateur.");
}

$statut_filter = $_GET['statut'] ?? null;
$demandes = DemandeVente::getAll($statut_filter);

$base_url = '/diamon_luxe';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Demandes de Vente | DIAMON</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .bg-gold { background-color: #C5A059; }
        .text-gold { color: #C5A059; }
    </style>
</head>
<body class="bg-gray-100">
    
    <!-- Navbar Admin -->
    <nav class="bg-black text-white p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-serif">DIAMON - Admin</h1>
            <div class="flex space-x-6 text-sm">
                <a href="<?= $base_url ?>/admin/produits.php" class="text-gold font-bold">Produits</a>
                <a href="<?= $base_url ?>/admin/codes_promo.php" class="hover:text-gold">Codes Promo</a>
                <a href="<?= $base_url ?>/admin/avis.php" class="hover:text-gold">Avis</a>
                <a href="<?= $base_url ?>/admin/commandes.php" class="hover:text-gold">Commandes</a>
                <a href="<?= $base_url ?>/admin/demandes_vente.php" class="hover:text-gold">Ventes</a>
                <a href="<?= $base_url ?>/admin/demandes_echange.php" class="hover:text-gold">Échanges</a>
                <a href="<?= $base_url ?>/index.php" class="hover:text-gold">Site</a>
                <a href="<?= $base_url ?>/admin/logout.php" class="hover:text-red-400">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-12">
        
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Demandes de Vente</h1>
            <div class="text-sm text-gray-600">
                <?= count($demandes) ?> demande<?= count($demandes) > 1 ? 's' : '' ?>
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <div class="flex gap-4">
                <a href="?statut=" 
                   class="px-4 py-2 rounded <?= !$statut_filter ? 'bg-black text-white' : 'bg-gray-200' ?>">
                    Toutes
                </a>
                <a href="?statut=en attente" 
                   class="px-4 py-2 rounded <?= $statut_filter == 'en attente' ? 'bg-yellow-500 text-white' : 'bg-gray-200' ?>">
                    En attente
                </a>
                <a href="?statut=en cours d'évaluation" 
                   class="px-4 py-2 rounded <?= $statut_filter == "en cours d'évaluation" ? 'bg-blue-500 text-white' : 'bg-gray-200' ?>">
                    En cours
                </a>
                <a href="?statut=acceptée" 
                   class="px-4 py-2 rounded <?= $statut_filter == 'acceptée' ? 'bg-green-500 text-white' : 'bg-gray-200' ?>">
                    Acceptées
                </a>
                <a href="?statut=refusée" 
                   class="px-4 py-2 rounded <?= $statut_filter == 'refusée' ? 'bg-red-500 text-white' : 'bg-gray-200' ?>">
                    Refusées
                </a>
                <a href="?statut=vendue" 
                   class="px-4 py-2 rounded <?= $statut_filter == 'vendue' ? 'bg-gray-700 text-white' : 'bg-gray-200' ?>">
                    Vendues
                </a>
            </div>
        </div>

        <!-- Liste des demandes -->
        <?php if (count($demandes) > 0): ?>
            <div class="space-y-6">
                <?php foreach ($demandes as $demande): ?>
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold mb-1">
                                        <?= htmlspecialchars($demande['marque']) ?> - <?= htmlspecialchars($demande['nom_produit']) ?>
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Demande #<?= $demande['id'] ?> • 
                                        <?= date('d/m/Y à H:i', strtotime($demande['created_at'])) ?>
                                    </p>
                                </div>
                                <div>
                                    <?php
                                    $statut_colors = [
                                        'en attente' => 'bg-yellow-100 text-yellow-800',
                                        "en cours d'évaluation" => 'bg-blue-100 text-blue-800',
                                        'acceptée' => 'bg-green-100 text-green-800',
                                        'refusée' => 'bg-red-100 text-red-800',
                                        'vendue' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $color = $statut_colors[$demande['statut']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="<?= $color ?> px-3 py-1 text-xs font-bold uppercase tracking-widest rounded">
                                        <?= htmlspecialchars($demande['statut']) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-6 mb-6">
                                <!-- Informations produit -->
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">Client</p>
                                        <p class="font-semibold"><?= htmlspecialchars($demande['prenom'] . ' ' . $demande['nom']) ?></p>
                                        <p class="text-sm text-gray-600"><?= htmlspecialchars($demande['email']) ?></p>
                                        <p class="text-sm text-gray-600"><?= htmlspecialchars($demande['telephone']) ?></p>
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

                                <!-- Photos -->
                                <div>
                                    <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-3">Photos</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <?php for ($i = 1; $i <= 4; $i++): ?>
                                            <?php if (!empty($demande["photo_$i"])): ?>
                                                <a href="<?= $base_url ?>/public/images/ventes/<?= $demande["photo_$i"] ?>" target="_blank">
                                                    <img src="<?= $base_url ?>/public/images/ventes/<?= $demande["photo_$i"] ?>" 
                                                         alt="Photo <?= $i ?>"
                                                         class="w-full h-32 object-cover rounded cursor-pointer hover:opacity-75 transition">
                                                </a>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-6">
                                <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-2">Description</p>
                                <p class="text-sm text-gray-700 bg-gray-50 p-4 rounded">
                                    <?= nl2br(htmlspecialchars($demande['description'])) ?>
                                </p>
                            </div>

                            <!-- Accessoires -->
                            <div class="mb-6 flex gap-6 text-sm">
                                <span class="<?= $demande['avec_boite'] ? 'text-green-600' : 'text-gray-400' ?>">
                                    <?= $demande['avec_boite'] ? '✓' : '✗' ?> Boîte d'origine
                                </span>
                                <span class="<?= $demande['avec_certificat'] ? 'text-green-600' : 'text-gray-400' ?>">
                                    <?= $demande['avec_certificat'] ? '✓' : '✗' ?> Certificat
                                </span>
                                <span class="<?= $demande['avec_accessoires'] ? 'text-green-600' : 'text-gray-400' ?>">
                                    <?= $demande['avec_accessoires'] ? '✓' : '✗' ?> Accessoires
                                </span>
                            </div>

                            <!-- Évaluation expert -->
                            <?php if ($demande['estimation_expert'] || $demande['commentaire_expert']): ?>
                                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                                    <p class="text-xs uppercase tracking-widest font-semibold text-blue-800 mb-2">Évaluation Expert</p>
                                    <?php if ($demande['estimation_expert']): ?>
                                        <p class="text-2xl font-serif text-blue-900 mb-2">
                                            <?= number_format($demande['estimation_expert'], 0, ',', ' ') ?> €
                                        </p>
                                    <?php endif; ?>
                                    <?php if ($demande['commentaire_expert']): ?>
                                        <p class="text-sm text-gray-700">
                                            <?= nl2br(htmlspecialchars($demande['commentaire_expert'])) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <a href="demande_vente_detail.php?id=<?= $demande['id'] ?>" 
                                   class="px-6 py-3 bg-black text-white text-sm font-semibold rounded hover:bg-gray-800">
                                    Gérer cette Demande
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <p class="text-gray-500 text-lg">Aucune demande de vente pour le moment.</p>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>