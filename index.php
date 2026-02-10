<?php 
$page_title = "DIAMON | Luxe, Achat, Vente & Échange";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/models/Produit.php';

// Récupérer les 3 derniers produits pour la section collection
$produits_recents = Produit::getAll(null, null, 3);
?>

<!-- Section Hero -->
<section class="relative h-screen flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1547949003-9792a18a2601?q=80&w=2070&auto=format&fit=crop" 
             class="w-full h-full object-cover scale-105" alt="Luxury bag">
        <div class="absolute inset-0 hero-gradient"></div>
    </div>

    <div class="relative z-10 text-center text-white px-4">
        <span class="block uppercase tracking-[0.4em] text-xs mb-6 opacity-90">L'Excellence Certifiée</span>
        <h1 class="text-5xl md:text-8xl font-serif mb-10 leading-tight italic">L'Art de la Transmission</h1>
        <div class="flex flex-col md:flex-row gap-6 justify-center uppercase text-[10px] font-bold tracking-[0.2em]">
            <a href="<?= $base_url ?>/catalogue.php" class="bg-white text-black px-12 py-5 hover:bg-gold hover:text-white transition duration-500">Explorer le Catalogue</a>
            <a href="<?= $base_url ?>/vendre.php" class="border border-white/40 backdrop-blur-sm px-12 py-5 hover:bg-white hover:text-black transition duration-500">Vendre une Pièce</a>
        </div>
    </div>
</section>

<!-- Section Vision -->
<section class="py-24 bg-[#F9F8F6]">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid lg:grid-cols-2 gap-20 items-center">
            <div>
                <h2 class="text-[10px] uppercase tracking-[0.4em] text-gold font-bold mb-4">Notre Vision</h2>
                <h3 class="text-4xl font-serif mb-8 leading-snug">Une classification rigoureuse pour une confiance absolue.</h3>
                <p class="text-gray-600 font-light leading-relaxed mb-8">
                    DIAMON redéfinit le marché de la seconde main de luxe. Nous ne nous contentons pas de vendre ; nous certifions la qualité à travers un système de grading strict, garantissant que chaque échange ou achat soit à la hauteur de vos exigences.
                </p>
                <div class="space-y-6">
                    <div class="flex items-start space-x-4">
                        <span class="font-serif text-2xl italic text-gold">A++</span>
                        <div>
                            <h4 class="font-bold text-xs uppercase tracking-widest">Grade A++ : État Boutique</h4>
                            <p class="text-sm text-gray-500">Neuf, jamais porté, avec certificat d'origine et scellés.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <span class="font-serif text-2xl italic text-gold">A+</span>
                        <div>
                            <h4 class="font-bold text-xs uppercase tracking-widest">Grade A+ : État Exceptionnel</h4>
                            <p class="text-sm text-gray-500">Proche du neuf, utilisé avec un soin extrême, accessoires inclus.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <span class="font-serif text-2xl italic text-gold">A</span>
                        <div>
                            <h4 class="font-bold text-xs uppercase tracking-widest">Grade A : Excellent État</h4>
                            <p class="text-sm text-gray-500">Très légères marques d'usage, structure et qualité préservées.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1584917865442-de89df76afd3?q=80&w=1935&auto=format&fit=crop" 
                     class="w-full h-[600px] object-cover shadow-2xl" alt="Authenticité">
                <div class="absolute -bottom-10 -left-10 bg-white p-10 hidden md:block border border-gray-100 shadow-xl">
                    <p class="text-4xl font-serif italic text-gold">100%</p>
                    <p class="text-[10px] uppercase font-bold tracking-widest">Expertisé</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Collection -->
<section id="collection" class="py-24 max-w-7xl mx-auto px-6">
    <div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-4">
        <div>
            <h2 class="text-4xl font-serif italic mb-2">Sélection Curatée</h2>
            <p class="text-gray-400 text-xs uppercase tracking-widest font-medium">Achetez, Vendez ou Échangez ces pièces iconiques</p>
        </div>
        <a href="<?= $base_url ?>/catalogue.php" class="text-[10px] font-bold uppercase tracking-widest hover:text-gold transition">
            Voir Tout le Catalogue →
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
        <?php foreach ($produits_recents as $produit): ?>
            <div class="group cursor-pointer">
                <a href="<?= $base_url ?>/views/produit_detail.php?id=<?= $produit['id'] ?>">
                    <div class="relative overflow-hidden aspect-[4/5] bg-gray-100 mb-6">
                        <?php 
                        $image_url = !empty($produit['image_principale']) 
                            ? $base_url . '/public/images/produits/' . $produit['image_principale']
                            : 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?q=80&w=1957&auto=format&fit=crop';
                        ?>
                        <img src="<?= $image_url ?>" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-700"
                             alt="<?= htmlspecialchars($produit['nom']) ?>">
                        <div class="absolute top-4 left-4 flex flex-col gap-2">
                            <span class="bg-black text-white px-3 py-1 text-[9px] font-bold tracking-widest uppercase">
                                Grade <?= htmlspecialchars($produit['grade_code']) ?>
                            </span>
                        </div>
                        <div class="absolute inset-x-0 bottom-0 bg-white/90 translate-y-full group-hover:translate-y-0 transition duration-300 p-4">
                            <button class="w-full py-3 bg-black text-white text-[10px] font-bold uppercase tracking-widest hover:bg-gold transition">
                                Voir les Détails
                            </button>
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
                        <span class="font-serif text-lg tracking-wider">
                            <?= number_format($produit['prix'], 0, ',', ' ') ?> €
                        </span>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<!-- Section Collection (remplacer uniquement cette partie) -->
<section id="collection" class="py-24 max-w-7xl mx-auto px-6">
    <div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-4">
        <div>
            <h2 class="text-4xl font-serif italic mb-2">Sélection Curatée</h2>
            <p class="text-gray-400 text-xs uppercase tracking-widest font-medium">Achetez, Vendez ou Échangez ces pièces iconiques</p>
        </div>
        <a href="<?= $base_url ?>/catalogue.php" class="text-[10px] font-bold uppercase tracking-widest hover:text-gold transition">
            Voir Tout le Catalogue →
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
        <?php foreach ($produits_recents as $produit): ?>
            <div class="group cursor-pointer">
                <div class="relative overflow-hidden aspect-[4/5] bg-gray-100 mb-6">
                    <?php 
                    $image_url = !empty($produit['image_principale']) 
                        ? $base_url . '/public/images/produits/' . $produit['image_principale']
                        : 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?q=80&w=1957&auto=format&fit=crop';
                    ?>
                    <img src="<?= $image_url ?>" 
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-700"
                         alt="<?= htmlspecialchars($produit['nom']) ?>">
                    <div class="absolute top-4 left-4 flex flex-col gap-2">
                        <span class="bg-black text-white px-3 py-1 text-[9px] font-bold tracking-widest uppercase">
                            Grade <?= htmlspecialchars($produit['grade_code']) ?>
                        </span>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 bg-white/95 translate-y-full group-hover:translate-y-0 transition duration-300 p-4 space-y-2">
                        <a href="<?= $base_url ?>/views/produit_detail.php?id=<?= $produit['id'] ?>"
                           class="block w-full py-3 bg-black text-white text-center text-[10px] font-bold uppercase tracking-widest hover:bg-gray-800 transition">
                            Voir les Détails
                        </a>
                        <a href="<?= $base_url ?>/echanger.php?id=<?= $produit['id'] ?>"
                           class="block w-full py-3 border-2 border-gold text-gold text-center text-[10px] font-bold uppercase tracking-widest hover:bg-gold hover:text-white transition">
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
                    <span class="font-serif text-lg tracking-wider">
                        <?= number_format($produit['prix'], 0, ',', ' ') ?> €
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>