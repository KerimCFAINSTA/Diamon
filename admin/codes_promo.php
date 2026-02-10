<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/CodePromo.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$codes = CodePromo::getAll();
$base_url = '/diamon_luxe';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codes Promo | DIAMON Admin</title>
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
            <div class="flex space-x-6 text-sm">
                <a href="<?= $base_url ?>/admin/dashboard.php" class="hover:text-gold">Dashboard</a>
                <a href="<?= $base_url ?>/admin/produits.php" class="hover:text-gold">Produits</a>
                <a href="<?= $base_url ?>/admin/codes_promo.php" class="text-gold font-bold">Codes Promo</a>
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
            <div>
                <h1 class="text-3xl font-bold mb-2">Codes Promo</h1>
                <p class="text-gray-600">Gérez vos promotions et réductions</p>
            </div>
            <a href="code_promo_ajouter.php" 
               class="bg-black text-white px-6 py-3 rounded text-sm font-bold uppercase tracking-widest hover:bg-gold transition">
                + Créer un Code Promo
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-800 px-6 py-4 rounded mb-8">
                ✓ Code promo créé avec succès !
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['updated'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-800 px-6 py-4 rounded mb-8">
                ✓ Code promo mis à jour !
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-800 px-6 py-4 rounded mb-8">
                ✓ Code promo supprimé !
            </div>
        <?php endif; ?>

        <?php if (count($codes) > 0): ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Réduction</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conditions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validité</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($codes as $code): ?>
                            <?php
                            $aujourdhui = date('Y-m-d');
                            $est_expire = $code['date_fin'] && $aujourdhui > $code['date_fin'];
                            $est_futur = $code['date_debut'] && $aujourdhui < $code['date_debut'];
                            $limite_atteinte = $code['limite_utilisation'] && $code['nb_utilisations'] >= $code['limite_utilisation'];
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-lg font-bold font-mono bg-gray-100 px-3 py-1 rounded">
                                            <?= htmlspecialchars($code['code']) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($code['type'] === 'pourcentage'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">
                                            % Pourcentage
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">
                                            € Montant Fixe
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-xl font-bold text-gold">
                                        <?php if ($code['type'] === 'pourcentage'): ?>
                                            -<?= $code['valeur'] ?>%
                                        <?php else: ?>
                                            -<?= number_format($code['valeur'], 0, ',', ' ') ?> €
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php if ($code['montant_minimum'] > 0): ?>
                                        <span class="text-gray-600">
                                            Min. <?= number_format($code['montant_minimum'], 0, ',', ' ') ?> €
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">Aucune</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php if ($code['date_debut'] || $code['date_fin']): ?>
                                        <?php if ($code['date_debut']): ?>
                                            Du <?= date('d/m/Y', strtotime($code['date_debut'])) ?>
                                        <?php endif; ?>
                                        <?php if ($code['date_fin']): ?>
                                            <br>Au <?= date('d/m/Y', strtotime($code['date_fin'])) ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">Illimitée</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="font-semibold"><?= $code['nb_utilisations'] ?></span>
                                    <?php if ($code['limite_utilisation']): ?>
                                        / <?= $code['limite_utilisation'] ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">/ ∞</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if (!$code['actif']): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800">
                                            Désactivé
                                        </span>
                                    <?php elseif ($est_expire): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded bg-red-100 text-red-800">
                                            Expiré
                                        </span>
                                    <?php elseif ($limite_atteinte): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded bg-orange-100 text-orange-800">
                                            Limite atteinte
                                        </span>
                                    <?php elseif ($est_futur): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded bg-yellow-100 text-yellow-800">
                                            À venir
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">
                                            ✓ Actif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="code_promo_modifier.php?id=<?= $code['id'] ?>" 
                                       class="text-blue-600 hover:text-blue-900">
                                        Modifier
                                    </a>
                                    <a href="code_promo_supprimer.php?id=<?= $code['id'] ?>" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce code promo ?')"
                                       class="text-red-600 hover:text-red-900">
                                        Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <div class="text-6xl mb-4">🎁</div>
                <p class="text-gray-500 text-lg mb-6">Aucun code promo pour le moment.</p>
                <a href="code_promo_ajouter.php" 
                   class="inline-block bg-black text-white px-8 py-3 rounded text-sm font-bold uppercase tracking-widest hover:bg-gold transition">
                    Créer le premier code promo
                </a>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>