<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/Avis.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$filtre_statut = $_GET['statut'] ?? null;
$avis_list = Avis::getAll($filtre_statut);

$base_url = '/diamon_luxe';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Avis | DIAMON Admin</title>
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
                <a href="<?= $base_url ?>/admin/codes_promo.php" class="hover:text-gold">Codes Promo</a>
                <a href="<?= $base_url ?>/admin/avis.php" class="text-gold font-bold"> Avis</a>
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
                <h1 class="text-3xl font-bold mb-2">Gestion des Avis Clients</h1>
                <p class="text-gray-600">Modérez et gérez les avis publiés sur vos produits</p>
            </div>
        </div>

        <?php if (isset($_GET['approved'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-800 px-6 py-4 rounded mb-8">
                ✓ Avis approuvé avec succès !
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['rejected'])): ?>
            <div class="bg-orange-100 border border-orange-400 text-orange-800 px-6 py-4 rounded mb-8">
                ⚠️ Avis rejeté.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-800 px-6 py-4 rounded mb-8">
                🗑️ Avis supprimé.
            </div>
        <?php endif; ?>

        <!-- Filtres -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <div class="flex items-center space-x-4">
                <span class="text-sm font-semibold">Filtrer par :</span>
                <a href="avis.php" 
                   class="px-4 py-2 rounded text-sm font-semibold <?= !$filtre_statut ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                    Tous (<?= count(Avis::getAll()) ?>)
                </a>
                <a href="avis.php?statut=en_attente" 
                   class="px-4 py-2 rounded text-sm font-semibold <?= $filtre_statut === 'en_attente' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                    En Attente (<?= count(Avis::getAll('en_attente')) ?>)
                </a>
                <a href="avis.php?statut=approuve" 
                   class="px-4 py-2 rounded text-sm font-semibold <?= $filtre_statut === 'approuve' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                    Approuvés (<?= count(Avis::getAll('approuve')) ?>)
                </a>
                <a href="avis.php?statut=rejete" 
                   class="px-4 py-2 rounded text-sm font-semibold <?= $filtre_statut === 'rejete' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                    Rejetés (<?= count(Avis::getAll('rejete')) ?>)
                </a>
            </div>
        </div>

        <!-- Liste des avis -->
        <?php if (count($avis_list) > 0): ?>
            <div class="space-y-6">
                <?php foreach ($avis_list as $avis): ?>
                    <?php $photos = Avis::getPhotos($avis['id']); ?>
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-6">
                            
                            <div class="flex items-start justify-between mb-4">
                                
                                <!-- Info client et produit -->
                                <div class="flex items-start space-x-4 flex-1">
                                    <!-- Image produit -->
                                    <div class="w-20 h-20 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                                        <?php if ($avis['image_principale']): ?>
                                            <img src="<?= $base_url ?>/public/images/produits/<?= $avis['image_principale'] ?>" 
                                                 class="w-full h-full object-cover"
                                                 alt="<?= htmlspecialchars($avis['produit_nom']) ?>">
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex-1">
                                        <!-- Produit -->
                                        <div class="mb-3">
                                            <p class="text-xs uppercase tracking-widest font-bold text-gray-500">
                                                <?= htmlspecialchars($avis['marque']) ?>
                                            </p>
                                            <p class="font-semibold">
                                                <?= htmlspecialchars($avis['produit_nom']) ?>
                                            </p>
                                        </div>
                                        
                                        <!-- Client -->
                                        <div class="text-sm text-gray-600 mb-2">
                                            <strong><?= htmlspecialchars($avis['prenom'] . ' ' . $avis['nom']) ?></strong>
                                            <span class="mx-2">·</span>
                                            <?= htmlspecialchars($avis['email']) ?>
                                            <?php if ($avis['verifie']): ?>
                                                <span class="text-green-600 ml-2">✓ Achat vérifié</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Date -->
                                        <p class="text-xs text-gray-500">
                                            Publié le <?= date('d/m/Y à H:i', strtotime($avis['created_at'])) ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Statut -->
                                <div>
                                    <?php
                                    $statut_styles = [
                                        'en_attente' => 'bg-yellow-100 text-yellow-800',
                                        'approuve' => 'bg-green-100 text-green-800',
                                        'rejete' => 'bg-red-100 text-red-800'
                                    ];
                                    $statut_labels = [
                                        'en_attente' => '⏳ En Attente',
                                        'approuve' => '✓ Approuvé',
                                        'rejete' => '✗ Rejeté'
                                    ];
                                    $style = $statut_styles[$avis['statut']] ?? 'bg-gray-100 text-gray-800';
                                    $label = $statut_labels[$avis['statut']] ?? $avis['statut'];
                                    ?>
                                    <span class="<?= $style ?> px-3 py-1 rounded-full text-xs font-semibold">
                                        <?= $label ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Note -->
                            <div class="flex items-center mb-3">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg class="w-6 h-6 <?= $i <= $avis['note'] ? 'text-gold' : 'text-gray-300' ?>" 
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                <?php endfor; ?>
                                <?php if ($avis['recommande']): ?>
                                    <span class="ml-3 bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">
                                        ✓ Recommande
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Titre -->
                            <h3 class="text-lg font-bold mb-2">
                                <?= htmlspecialchars($avis['titre']) ?>
                            </h3>

                            <!-- Commentaire -->
                            <p class="text-gray-600 leading-relaxed mb-4">
                                <?= nl2br(htmlspecialchars($avis['commentaire'])) ?>
                            </p>

                            <!-- Photos -->
                            <?php if (count($photos) > 0): ?>
                                <div class="flex gap-2 mb-4 pb-4 border-b border-gray-200">
                                    <?php foreach ($photos as $photo): ?>
                                        <img src="<?= $base_url ?>/public/images/avis/<?= $photo['chemin'] ?>" 
                                             class="w-24 h-24 object-cover rounded cursor-pointer hover:opacity-75 transition"
                                             onclick="window.open(this.src)"
                                             alt="Photo avis">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Actions -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <div class="flex items-center space-x-3">
                                    <?php if ($avis['statut'] !== 'approuve'): ?>
                                        <a href="avis_statut.php?id=<?= $avis['id'] ?>&action=approuver" 
                                           class="px-4 py-2 bg-green-500 text-white rounded text-sm font-semibold hover:bg-green-600 transition">
                                            ✓ Approuver
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($avis['statut'] !== 'rejete'): ?>
                                        <a href="avis_statut.php?id=<?= $avis['id'] ?>&action=rejeter" 
                                           class="px-4 py-2 bg-orange-500 text-white rounded text-sm font-semibold hover:bg-orange-600 transition">
                                            ✗ Rejeter
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="avis_detail.php?id=<?= $avis['id'] ?>" 
                                       class="px-4 py-2 bg-blue-500 text-white rounded text-sm font-semibold hover:bg-blue-600 transition">
                                        👁️ Détails
                                    </a>
                                </div>
                                
                                <a href="avis_supprimer.php?id=<?= $avis['id'] ?>" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')"
                                   class="px-4 py-2 bg-red-500 text-white rounded text-sm font-semibold hover:bg-red-600 transition">
                                    🗑️ Supprimer
                                </a>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <div class="text-6xl mb-4">⭐</div>
                <p class="text-gray-500 text-lg mb-2">Aucun avis<?= $filtre_statut ? ' ' . $filtre_statut : '' ?></p>
                <p class="text-sm text-gray-400">Les avis clients apparaîtront ici</p>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>