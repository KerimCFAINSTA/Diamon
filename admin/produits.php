<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/Produit.php';
require_once __DIR__ . '/../models/Categorie.php';
require_once __DIR__ . '/../models/Grade.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

// Récupérer tous les produits
$produits = Produit::getAll();

$base_url = '/diamon_luxe';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits | DIAMON Admin</title>
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
            <div class="flex justify-between items-center mb-8">
    
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-800 px-6 py-4 rounded mb-8">
        ✓ Produit ajouté avec succès !
    </div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-800 px-6 py-4 rounded mb-8">
        ✓ Produit supprimé avec succès !
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded mb-8">
        ❌ Une erreur est survenue.
    </div>
<?php endif; ?>
            <div class="flex space-x-6 text-sm">
                <a href="<?= $base_url ?>/admin/dashboard.php" class="hover:text-gold">Dashboard</a>
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
            <h1 class="text-3xl font-bold">Gestion des Produits</h1>
            <a href="produit_ajouter.php" 
               class="bg-black text-white px-6 py-3 rounded text-sm font-bold uppercase tracking-widest hover:bg-gold transition">
                + Ajouter un Produit
            </a>
        </div>

        <?php if (count($produits) > 0): ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($produits as $produit): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $produit['id'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <?php if (!empty($produit['image_principale'])): ?>
                                                <img class="h-12 w-12 rounded object-cover" 
                                                     src="<?= $base_url ?>/public/images/produits/<?= $produit['image_principale'] ?>" 
                                                     alt="<?= htmlspecialchars($produit['nom']) ?>">
                                            <?php else: ?>
                                                <div class="h-12 w-12 rounded bg-gray-200 flex items-center justify-center">
                                                    <span class="text-gray-400 text-xs">Pas d'image</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($produit['marque']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($produit['nom']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($produit['categorie_nom']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded bg-black text-white">
                                        Grade <?= htmlspecialchars($produit['grade_code']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gold">
                                    <?= number_format($produit['prix'], 0, ',', ' ') ?> €
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $produit['stock'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($produit['disponible'] && $produit['stock'] > 0): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Disponible
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Indisponible
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="produit_modifier.php?id=<?= $produit['id'] ?>" 
                                       class="text-blue-600 hover:text-blue-900">
                                        Modifier
                                    </a>
                                    <a href="produit_supprimer.php?id=<?= $produit['id'] ?>" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')"
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
                <p class="text-gray-500 text-lg mb-6">Aucun produit pour le moment.</p>
                <a href="produit_ajouter.php" 
                   class="inline-block bg-black text-white px-8 py-3 rounded text-sm font-bold uppercase tracking-widest hover:bg-gold transition">
                    Ajouter le premier produit
                </a>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>