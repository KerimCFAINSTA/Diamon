<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/DemandeEchange.php';

// Protection admin
if (!isset($_SESSION['admin'])) {
    // Redirection simple vers une page de login admin ou afficher un message
    echo "<!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <title>Accès Refusé</title>
        <style>
            body { font-family: Arial; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #f5f5f5; }
            .container { text-align: center; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #C5A059; }
            .btn { display: inline-block; margin-top: 20px; padding: 12px 24px; background: #000; color: white; text-decoration: none; border-radius: 4px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>🔒 Accès Refusé</h1>
            <p>Vous devez être administrateur pour accéder à cette page.</p>
            <a href='/diamon_luxe/admin/login.php' class='btn'>Se connecter en tant qu'admin</a>
        </div>
    </body>
    </html>";
    exit();
}

$statut_filter = $_GET['statut'] ?? null;
$demandes = DemandeEchange::getAll($statut_filter);

$base_url = '/diamon_luxe';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Demandes d'Échange | DIAMON</title>
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
                <a href="<?= $base_url ?>/admin/produits.php" class="hover:text-gold">Produits</a>
                <a href="<?= $base_url ?>/admin/codes_promo.php" class="hover:text-gold">Codes Promo</a>
                <a href="<?= $base_url ?>/admin/avis.php" class="hover:text-gold">Avis</a>
                <a href="<?= $base_url ?>/admin/commandes.php" class="hover:text-gold">Commandes</a>
                <a href="<?= $base_url ?>/admin/demandes_vente.php" class="hover:text-gold">Ventes</a>
                <a href="<?= $base_url ?>/admin/demandes_echange.php" class="text-gold font-bold">Échanges</a>
                <a href="<?= $base_url ?>/index.php" class="hover:text-gold">Site</a>
                <a href="<?= $base_url ?>/admin/logout.php" class="hover:text-red-400">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-12">
        
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Demandes d'Échange</h1>
            <div class="text-sm text-gray-600">
                <?= count($demandes) ?> demande<?= count($demandes) > 1 ? 's' : '' ?>
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <div class="flex gap-4 flex-wrap">
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
                <a href="?statut=échange effectué" 
                   class="px-4 py-2 rounded <?= $statut_filter == 'échange effectué' ? 'bg-gray-700 text-white' : 'bg-gray-200' ?>">
                    Effectués
                </a>
            </div>
        </div>

        <!-- Liste des demandes -->
        <?php if (count($demandes) > 0): ?>
            <div class="space-y-6">
                <?php foreach ($demandes as $demande): ?>
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <h3 class="text-xl font-bold mb-1">
                                        Échange #<?= $demande['id'] ?>
                                    </h3>
                                    <p class="text-sm text-gray-600">
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
                                        'échange effectué' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $color = $statut_colors[$demande['statut']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="<?= $color ?> px-3 py-1 text-xs font-bold uppercase tracking-widest rounded">
                                        <?= htmlspecialchars($demande['statut']) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-3 gap-6 mb-6">
                                
                                <!-- Client -->
                                <div class="bg-gray-50 p-4 rounded">
                                    <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-2">Client</p>
                                    <p class="font-semibold mb-1"><?= htmlspecialchars($demande['prenom'] . ' ' . $demande['nom']) ?></p>
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($demande['email']) ?></p>
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($demande['telephone']) ?></p>
                                </div>

                                <!-- Produit Proposé -->
                                <div class="bg-blue-50 p-4 rounded">
                                    <p class="text-xs uppercase tracking-widest font-semibold text-blue-800 mb-2">Article Proposé</p>
                                    <p class="font-bold text-sm"><?= htmlspecialchars($demande['marque_propose']) ?></p>
                                    <p class="text-sm"><?= htmlspecialchars($demande['nom_produit_propose']) ?></p>
                                    <p class="text-xs text-gray-600 mt-2">Grade <?= htmlspecialchars($demande['etat_estime']) ?></p>
                                    <?php if ($demande['valeur_estimee']): ?>
                                        <p class="text-lg font-serif text-gold mt-2">
                                            <?= number_format($demande['valeur_estimee'], 0, ',', ' ') ?> €
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <!-- Produit Souhaité -->
                                <div class="bg-gold bg-opacity-10 p-4 rounded">
                                    <p class="text-xs uppercase tracking-widest font-semibold text-gray-700 mb-2">Article Souhaité</p>
                                    <p class="font-bold text-sm"><?= htmlspecialchars($demande['produit_souhaite_marque']) ?></p>
                                    <p class="text-sm"><?= htmlspecialchars($demande['produit_souhaite_nom']) ?></p>
                                </div>

                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <a href="demande_echange_detail.php?id=<?= $demande['id'] ?>" 
                                   class="px-6 py-3 bg-black text-white text-sm font-semibold rounded hover:bg-gray-800">
                                    Gérer cet Échange
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <p class="text-gray-500 text-lg">Aucune demande d'échange pour le moment.</p>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>