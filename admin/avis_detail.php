<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/Avis.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: avis.php');
    exit();
}

$avis = Avis::getById($id);

if (!$avis) {
    header('Location: avis.php');
    exit();
}

$photos = Avis::getPhotos($id);
$base_url = '/diamon_luxe';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Avis #<?= $id ?> | DIAMON Admin</title>
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
            <a href="avis.php" class="text-sm hover:text-gold">← Retour aux avis</a>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-6 py-12">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Détails de l'Avis #<?= $id ?></h1>
            <p class="text-gray-600">Informations complètes sur cet avis client</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            
            <!-- Contenu principal -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Avis -->
                <div class="bg-white rounded-lg shadow p-8">
                    
                    <!-- Note -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg class="w-8 h-8 <?= $i <= $avis['note'] ? 'text-gold' : 'text-gray-300' ?>" 
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            <?php endfor; ?>
                            <span class="ml-3 text-2xl font-bold"><?= $avis['note'] ?>/5</span>
                        </div>
                        
                        <?php if ($avis['recommande']): ?>
                            <span class="bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-semibold">
                                ✓ Recommande ce produit
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Titre -->
                    <h2 class="text-2xl font-bold mb-4">
                        <?= htmlspecialchars($avis['titre']) ?>
                    </h2>

                    <!-- Commentaire -->
                    <div class="mb-6">
                        <p class="text-gray-600 leading-relaxed whitespace-pre-wrap">
                            <?= htmlspecialchars($avis['commentaire']) ?>
                        </p>
                    </div>

                    <!-- Photos -->
                    <?php if (count($photos) > 0): ?>
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="font-bold mb-4">Photos (<?= count($photos) ?>)</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <?php foreach ($photos as $photo): ?>
                                    <a href="<?= $base_url ?>/public/images/avis/<?= $photo['chemin'] ?>" 
                                       target="_blank"
                                       class="block">
                                        <img src="<?= $base_url ?>/public/images/avis/<?= $photo['chemin'] ?>" 
                                             class="w-full aspect-square object-cover rounded hover:opacity-75 transition"
                                             alt="Photo avis">
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- Produit concerné -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold mb-4">Produit Concerné</h3>
                    <div class="flex items-center space-x-4">
                        <div class="w-24 h-24 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                            <?php if ($avis['image_principale']): ?>
                                <img src="<?= $base_url ?>/public/images/produits/<?= $avis['image_principale'] ?>" 
                                     class="w-full h-full object-cover"
                                     alt="<?= htmlspecialchars($avis['produit_nom']) ?>">
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest font-bold text-gray-500">
                                <?= htmlspecialchars($avis['marque']) ?>
                            </p>
                            <h4 class="text-lg font-semibold">
                                <?= htmlspecialchars($avis['produit_nom']) ?>
                            </h4>
                            <a href="<?= $base_url ?>/views/produit_detail.php?id=<?= $avis['id_produit'] ?>" 
                               target="_blank"
                               class="text-sm text-gold hover:underline">
                                Voir le produit →
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Statut -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold mb-4">Statut</h3>
                    <?php
                    $statut_styles = [
                        'en_attente' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                        'approuve' => 'bg-green-100 text-green-800 border-green-300',
                        'rejete' => 'bg-red-100 text-red-800 border-red-300'
                    ];
                    $statut_labels = [
                        'en_attente' => '⏳ En Attente',
                        'approuve' => '✓ Approuvé',
                        'rejete' => '✗ Rejeté'
                    ];
                    $style = $statut_styles[$avis['statut']] ?? 'bg-gray-100 text-gray-800 border-gray-300';
                    $label = $statut_labels[$avis['statut']] ?? $avis['statut'];
                    ?>
                    <div class="border-2 <?= $style ?> rounded-lg p-4 text-center font-bold mb-4">
                        <?= $label ?>
                    </div>
                    
                    <div class="space-y-2">
                        <?php if ($avis['statut'] !== 'approuve'): ?>
                            <a href="avis_statut.php?id=<?= $id ?>&action=approuver" 
                               class="block w-full px-4 py-3 bg-green-500 text-white text-center rounded font-semibold hover:bg-green-600 transition">
                                ✓ Approuver
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($avis['statut'] !== 'rejete'): ?>
                            <a href="avis_statut.php?id=<?= $id ?>&action=rejeter" 
                               class="block w-full px-4 py-3 bg-orange-500 text-white text-center rounded font-semibold hover:bg-orange-600 transition">
                                ✗ Rejeter
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Informations Client -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold mb-4">Client</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-gray-500 text-xs">Nom</p>
                            <p class="font-semibold">
                                <?= htmlspecialchars($avis['prenom'] . ' ' . $avis['nom']) ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs">Email</p>
                            <p class="font-semibold">
                                <?= htmlspecialchars($avis['email']) ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs">Achat vérifié</p>
                            <p class="font-semibold">
                                <?php if ($avis['verifie']): ?>
                                    <span class="text-green-600">✓ Oui</span>
                                <?php else: ?>
                                    <span class="text-orange-600">⚠️ Non</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Dates -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold mb-4">Dates</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-gray-500 text-xs">Publié le</p>
                            <p class="font-semibold">
                                <?= date('d/m/Y à H:i', strtotime($avis['created_at'])) ?>
                            </p>
                        </div>
                        <?php if ($avis['date_achat']): ?>
                            <div>
                                <p class="text-gray-500 text-xs">Date d'achat</p>
                                <p class="font-semibold">
                                    <?= date('d/m/Y', strtotime($avis['date_achat'])) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions dangereuses -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold mb-4 text-red-600">Zone Dangereuse</h3>
                    <a href="avis_supprimer.php?id=<?= $id ?>" 
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer définitivement cet avis ?')"
                       class="block w-full px-4 py-3 bg-red-500 text-white text-center rounded font-semibold hover:bg-red-600 transition">
                        🗑️ Supprimer l'Avis
                    </a>
                </div>

            </div>

        </div>

    </div>

</body>
</html>