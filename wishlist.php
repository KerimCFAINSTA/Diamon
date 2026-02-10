<?php
$page_title = "Ma Liste de Souhaits | DIAMON";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/models/Wishlist.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['client_id'])) {
    header('Location: ' . $base_url . '/connexion.php?redirect=wishlist.php');
    exit();
}

$id_client = $_SESSION['client_id'];
$produits = Wishlist::getProduitsWishlist($id_client);
?>

<main class="pt-32 pb-24">
    <div class="max-w-7xl mx-auto px-6">
        
        <div class="mb-12">
            <h1 class="text-4xl font-serif italic mb-2">Ma Liste de Souhaits</h1>
            <p class="text-gray-600">
                <?= count($produits) ?> produit<?= count($produits) > 1 ? 's' : '' ?> enregistré<?= count($produits) > 1 ? 's' : '' ?>
            </p>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded mb-8">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded mb-8">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (count($produits) > 0): ?>
            
            <!-- Actions groupées -->
            <div class="flex items-center justify-between mb-8 p-6 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600">
                    Vous avez <strong><?= count($produits) ?></strong> produit<?= count($produits) > 1 ? 's' : '' ?> dans votre liste
                </p>
                <div class="flex items-center space-x-4">
                    <a href="<?= $base_url ?>/wishlist_action.php?action=vers_panier" 
                       onclick="return confirm('Ajouter tous les produits disponibles au panier ?')"
                       class="px-6 py-3 bg-gold text-white rounded text-sm font-semibold hover:bg-black transition">
                        🛒 Tout Ajouter au Panier
                    </a>
                    <a href="<?= $base_url ?>/wishlist_action.php?action=vider" 
                       onclick="return confirm('Êtes-vous sûr de vouloir vider votre liste de souhaits ?')"
                       class="px-6 py-3 border border-gray-300 rounded text-sm font-semibold hover:bg-gray-100 transition">
                        🗑️ Vider la Liste
                    </a>
                </div>
            </div>

            <!-- Grille de produits -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <?php foreach ($produits as $produit): ?>
                    <div class="group relative bg-white rounded-lg shadow hover:shadow-lg transition">
                        
                        <!-- Bouton Retirer (coeur) -->
                        <button onclick="retirerWishlist(<?= $produit['id'] ?>)"
                                class="absolute top-4 right-4 z-10 w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center hover:bg-red-50 transition group/heart">
                            <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                            </svg>
                        </button>

                        <a href="<?= $base_url ?>/views/produit_detail.php?id=<?= $produit['id'] ?>">
                            <!-- Image -->
                            <div class="relative overflow-hidden aspect-square bg-gray-100 rounded-t-lg">
                                <?php 
                                $image_url = !empty($produit['image_principale']) 
                                    ? $base_url . '/public/images/produits/' . $produit['image_principale']
                                    : 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?q=80&w=1957&auto=format&fit=crop';
                                ?>
                                <img src="<?= $image_url ?>" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-700"
                                     alt="<?= htmlspecialchars($produit['nom']) ?>">
                                
                                <!-- Badge Grade -->
                                <div class="absolute top-4 left-4">
                                    <span class="bg-black text-white px-3 py-1 text-[9px] font-bold tracking-widest uppercase rounded">
                                        Grade <?= htmlspecialchars($produit['grade_code']) ?>
                                    </span>
                                </div>

                                <!-- Badge Disponibilité -->
                                <?php if (!$produit['disponible'] || $produit['stock'] <= 0): ?>
                                    <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                                        <span class="bg-red-500 text-white px-4 py-2 rounded font-bold text-sm">
                                            INDISPONIBLE
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Infos produit -->
                            <div class="p-4">
                                <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-1">
                                    <?= htmlspecialchars($produit['marque']) ?>
                                </p>
                                <h3 class="text-sm font-medium text-gray-900 mb-2 line-clamp-2">
                                    <?= htmlspecialchars($produit['nom']) ?>
                                </h3>
                                
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-lg font-serif text-gold">
                                        <?= number_format($produit['prix'], 0, ',', ' ') ?> €
                                    </span>
                                    
                                    <?php if ($produit['note_moyenne']): ?>
                                        <div class="flex items-center text-xs">
                                            <span class="text-gold mr-1">⭐</span>
                                            <span class="font-semibold"><?= number_format($produit['note_moyenne'], 1) ?></span>
                                            <span class="text-gray-400 ml-1">(<?= $produit['nb_avis'] ?>)</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <p class="text-xs text-gray-500 mb-3">
                                    Ajouté le <?= date('d/m/Y', strtotime($produit['date_ajout'])) ?>
                                </p>
                            </div>
                        </a>

                        <!-- Boutons d'action -->
                        <div class="p-4 pt-0 space-y-2">
                            <?php if ($produit['disponible'] && $produit['stock'] > 0): ?>
                                <a href="<?= $base_url ?>/panier_ajouter.php?id=<?= $produit['id'] ?>&quantite=1" 
                                   class="block w-full py-3 bg-black text-white text-center text-xs font-bold uppercase tracking-widest hover:bg-gold transition rounded">
                                    Ajouter au Panier
                                </a>
                            <?php else: ?>
                                <button disabled 
                                        class="block w-full py-3 bg-gray-300 text-gray-500 text-center text-xs font-bold uppercase tracking-widest cursor-not-allowed rounded">
                                    Indisponible
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            
            <!-- Liste vide -->
            <div class="text-center py-20">
                <div class="text-6xl mb-4">💔</div>
                <h2 class="text-2xl font-bold mb-2">Votre liste de souhaits est vide</h2>
                <p class="text-gray-600 mb-8">Ajoutez des produits pour les retrouver facilement plus tard</p>
                <a href="<?= $base_url ?>/catalogue.php" 
                   class="inline-block bg-black text-white px-8 py-4 text-sm font-bold uppercase tracking-widest hover:bg-gold transition rounded">
                    Découvrir nos Produits
                </a>
            </div>

        <?php endif; ?>

    </div>
</main>

<script>
    function retirerWishlist(idProduit) {
        if (confirm('Retirer ce produit de votre liste de souhaits ?')) {
            window.location.href = `<?= $base_url ?>/wishlist_action.php?action=retirer&id=${idProduit}`;
        }
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>