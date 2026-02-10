<?php 
$page_title = "Catalogue | DIAMON";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/models/Produit.php';
require_once __DIR__ . '/models/Categorie.php';
require_once __DIR__ . '/models/Grade.php';

// Récupération des filtres
$categorie_filter = $_GET['categorie'] ?? null;
$grade_filter = $_GET['grade'] ?? null;
$search = $_GET['search'] ?? null;

// Récupération des produits
if ($search) {
    $produits = Produit::search($search);
} else {
    $produits = Produit::getAll($categorie_filter, $grade_filter);
}

$categories = Categorie::getAll();
$grades = Grade::getAll();
?>

<main class="pt-32 pb-24">
    <div class="max-w-7xl mx-auto px-6">
        
        <!-- En-tête -->
        <div class="text-center mb-12">
            <h1 class="text-5xl font-serif italic mb-4">Notre Collection</h1>
            <p class="text-gray-500 text-sm mb-4">Découvrez notre sélection de pièces de luxe certifiées</p>
            
            <!-- Lien vers recherche avancée -->
            <a href="<?= $base_url ?>/recherche.php" 
               class="inline-flex items-center text-sm text-gold hover:underline transition group">
                <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Recherche avancée avec filtres
            </a>
        </div>

        <!-- Barre de recherche et filtres -->
        <div class="mb-12">
            <form method="get" class="flex flex-col md:flex-row gap-4 items-center justify-between">
                
                <!-- Recherche -->
                <div class="w-full md:w-1/3">
                    <input type="text" 
                           name="search" 
                           value="<?= htmlspecialchars($search ?? '') ?>"
                           placeholder="Rechercher une marque, un produit..." 
                           class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none text-sm rounded">
                </div>

                <!-- Filtres -->
                <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                    <select name="categorie" 
                            class="px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none text-xs uppercase tracking-widest font-semibold rounded"
                            onchange="this.form.submit()">
                        <option value="">Toutes Catégories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['slug'] ?>" <?= $categorie_filter == $cat['slug'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nom']) ?> (<?= $cat['nb_produits'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="grade" 
                            class="px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none text-xs uppercase tracking-widest font-semibold rounded"
                            onchange="this.form.submit()">
                        <option value="">Tous Grades</option>
                        <?php foreach ($grades as $g): ?>
                            <option value="<?= $g['code'] ?>" <?= $grade_filter == $g['code'] ? 'selected' : '' ?>>
                                Grade <?= htmlspecialchars($g['code']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="bg-black text-white px-8 py-3 text-[10px] font-bold uppercase tracking-widest hover:bg-gold transition rounded">
                        Filtrer
                    </button>
                </div>
            </form>

            <?php if ($categorie_filter || $grade_filter || $search): ?>
                <div class="mt-4 text-center">
                    <a href="<?= $base_url ?>/catalogue.php" class="text-xs text-gray-500 hover:text-gold underline">
                        Réinitialiser les filtres
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Nombre de résultats -->
        <div class="mb-8 flex items-center justify-between">
            <p class="text-sm text-gray-600">
                <strong><?= count($produits) ?></strong> produit<?= count($produits) > 1 ? 's' : '' ?> trouvé<?= count($produits) > 1 ? 's' : '' ?>
            </p>
            
            <!-- Badge "Recherche avancée" -->
            <a href="<?= $base_url ?>/recherche.php" 
               class="text-xs bg-gold text-white px-4 py-2 rounded-full hover:bg-black transition font-semibold">
                ⚡ Filtres avancés disponibles
            </a>
        </div>

        <!-- Grille de produits -->
        <?php if (count($produits) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                <?php foreach ($produits as $produit): ?>
                    <div class="group cursor-pointer relative">
                        
                        <!-- Bouton Wishlist flottant -->
                        <div class="absolute top-4 right-4 z-10">
                            <?php 
                            $id_produit = $produit['id'];
                            $show_text = false;
                            include __DIR__ . '/components/wishlist_button.php'; 
                            ?>
                        </div>
                        
                        <div class="relative overflow-hidden aspect-[4/5] bg-gray-100 mb-6 rounded-lg">
                            <?php 
                            $image_url = !empty($produit['image_principale']) 
                                ? $base_url . '/public/images/produits/' . $produit['image_principale']
                                : 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?q=80&w=1957&auto=format&fit=crop';
                            ?>
                            <img src="<?= $image_url ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-700"
                                 alt="<?= htmlspecialchars($produit['nom']) ?>">
                            
                            <div class="absolute top-4 left-4 flex flex-col gap-2">
                                <span class="bg-black text-white px-3 py-1 text-[9px] font-bold tracking-widest uppercase rounded">
                                    Grade <?= htmlspecialchars($produit['grade_code']) ?>
                                </span>
                                <span class="bg-gold text-white px-3 py-1 text-[9px] font-bold tracking-widest uppercase rounded">
                                    <?= htmlspecialchars($produit['categorie_nom']) ?>
                                </span>
                            </div>

                            <!-- Boutons au survol -->
                            <div class="absolute inset-x-0 bottom-0 bg-white/95 translate-y-full group-hover:translate-y-0 transition duration-300 p-4 space-y-2">
                                <a href="<?= $base_url ?>/views/produit_detail.php?id=<?= $produit['id'] ?>"
                                   class="block w-full py-3 bg-black text-white text-center text-[10px] font-bold uppercase tracking-widest hover:bg-gray-800 transition rounded">
                                    Voir les Détails
                                </a>
                                <a href="<?= $base_url ?>/echanger.php?id=<?= $produit['id'] ?>"
                                   class="block w-full py-3 border-2 border-gold text-gold text-center text-[10px] font-bold uppercase tracking-widest hover:bg-gold hover:text-white transition rounded">
                                    Proposer un Échange
                                </a>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-bold text-xs uppercase tracking-widest mb-1">
                                    <?= htmlspecialchars($produit['marque']) ?>
                                </h4>
                                <p class="text-gray-500 font-light text-sm italic">
                                    <?= htmlspecialchars($produit['nom']) ?>
                                </p>
                            </div>
                            <span class="font-serif text-lg tracking-wider text-gold">
                                <?= number_format($produit['prix'], 0, ',', ' ') ?> €
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20">
                <div class="text-6xl mb-4">🔍</div>
                <p class="text-gray-500 text-lg mb-6">Aucun produit ne correspond à vos critères.</p>
                <div class="flex items-center justify-center space-x-4">
                    <a href="<?= $base_url ?>/catalogue.php" 
                       class="bg-black text-white px-8 py-3 rounded text-sm font-semibold uppercase tracking-widest hover:bg-gold transition">
                        Voir tous les produits
                    </a>
                    <a href="<?= $base_url ?>/recherche.php" 
                       class="border-2 border-gold text-gold px-8 py-3 rounded text-sm font-semibold uppercase tracking-widest hover:bg-gold hover:text-white transition">
                        Recherche avancée
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>