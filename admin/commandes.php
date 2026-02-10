<?php
session_start();

$base_url = '/diamon_luxe';

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/Commande.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: ../login.php');
    exit;
}

$commandes = Commande::getAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Commandes | DIAMON</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<nav class="bg-black text-white p-4">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <h1 class="text-2xl font-serif">DIAMON - Admin</h1>
        <div class="flex space-x-6 text-sm">
            <a href="<?= $base_url ?>/admin/dashboard.php" class="hover:text-gold">Dashboard</a>
            <a href="<?= $base_url ?>/admin/produits.php">Produits</a>
            <a href="<?= $base_url ?>/admin/codes_promo.php" class="hover:text-gold">Codes Promo</a>
            <a href="<?= $base_url ?>/admin/avis.php" class="hover:text-gold">Avis</a>
            <a href="<?= $base_url ?>/admin/commandes.php">Commandes</a>
            <a href="<?= $base_url ?>/admin/demandes_vente.php">Ventes</a>
            <a href="<?= $base_url ?>/admin/demandes_echange.php">Échanges</a>
            <a href="<?= $base_url ?>/index.php">Site</a>
            <a href="<?= $base_url ?>/admin/logout.php" class="text-red-400">Déconnexion</a>
        </div>
    </div>
</nav>



    
    <div class="max-w-7xl mx-auto px-6 py-12">
        
        <h1 class="text-3xl font-bold mb-8">Gestion des Commandes</h1>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numéro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($commandes as $cmd): ?>
                        <tr>
                            <td class="px-6 py-4 text-sm font-semibold">#<?= htmlspecialchars($cmd['numero_commande']) ?></td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold"><?= htmlspecialchars($cmd['prenom'] . ' ' . $cmd['nom']) ?></div>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($cmd['email']) ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm"><?= date('d/m/Y H:i', strtotime($cmd['created_at'])) ?></td>
                            <td class="px-6 py-4 text-sm font-semibold"><?= number_format($cmd['montant_total'], 0, ',', ' ') ?> €</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800">
                                    <?= htmlspecialchars($cmd['statut']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="commande_detail.php?id=<?= $cmd['id'] ?>" class="text-blue-600 hover:underline">
                                    Détails
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>