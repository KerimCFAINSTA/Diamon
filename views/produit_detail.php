<?php 
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Produit.php';

// Récupération de l'ID du produit
$id_produit = $_GET['id'] ?? null;

if (!$id_produit) {
    header('Location: ' . $base_url . '/catalogue.php');
    exit();
}

$produit = Produit::getById($id_produit);

if (!$produit) {
    header('Location: ' . $base_url . '/catalogue.php');
    exit();
}

$images = Produit::getImages($id_produit);
$page_title = htmlspecialchars($produit['marque'] . ' - ' . $produit['nom']) . ' | DIAMON';
?>

<main class="pt-32 pb-24">
    <div class="max-w-7xl mx-auto px-6">
        
        <!-- Fil d'Ariane -->
        <nav class="mb-8 text-xs uppercase tracking-widest text-gray-500">
            <a href="<?= $base_url ?>/index.php" class="hover:text-gold">Accueil</a>
            <span class="mx-2">/</span>
            <a href="<?= $base_url ?>/catalogue.php" class="hover:text-gold">Catalogue</a>
            <span class="mx-2">/</span>
            <a href="<?= $base_url ?>/catalogue.php?categorie=<?= $produit['categorie_slug'] ?>" class="hover:text-gold">
                <?= htmlspecialchars($produit['categorie_nom']) ?>
            </a>
            <span class="mx-2">/</span>
            <span class="text-black"><?= htmlspecialchars($produit['nom']) ?></span>
        </nav>

        <!-- Message de confirmation -->
        <?php if (isset($_SESSION['panier_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded mb-8 flex items-center justify-between">
                <span>✓ <?= htmlspecialchars($_SESSION['panier_message']) ?></span>
                <a href="<?= $base_url ?>/views/panier.php" class="underline font-semibold hover:text-green-900">
                    Voir le panier →
                </a>
            </div>
            <?php unset($_SESSION['panier_message']); ?>
        <?php endif; ?>

        <div class="grid lg:grid-cols-2 gap-16">
            
            <!-- Galerie d'images -->
            <div>
                <div class="sticky top-32">
                    <?php 
                    $image_principale = !empty($produit['image_principale']) 
                        ? $base_url . '/public/images/produits/' . $produit['image_principale']
                        : 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?q=80&w=1957&auto=format&fit=crop';
                    ?>
                    <img src="<?= $image_principale ?>" 
                         id="imagePrincipale"
                         alt="<?= htmlspecialchars($produit['nom']) ?>"
                         class="w-full aspect-square object-cover mb-4 shadow-lg rounded">
                    
                    <!-- Miniatures (si plusieurs images) -->
                    <?php if (count($images) > 0): ?>
                        <div class="grid grid-cols-4 gap-4">
                            <img src="<?= $image_principale ?>" 
                                 onclick="changerImage('<?= $image_principale ?>')"
                                 class="w-full aspect-square object-cover cursor-pointer border-2 border-gold hover:opacity-75 transition rounded"
                                 alt="Image principale">
                            <?php foreach ($images as $img): ?>
                                <img src="<?= $base_url ?>/public/images/produits/<?= $img['chemin'] ?>" 
                                     onclick="changerImage('<?= $base_url ?>/public/images/produits/<?= $img['chemin'] ?>')"
                                     class="w-full aspect-square object-cover cursor-pointer border-2 border-gray-200 hover:border-gold transition rounded"
                                     alt="Image produit">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Informations produit -->
            <div>
                <div class="mb-6">
                    <span class="inline-block bg-black text-white px-4 py-2 text-[9px] font-bold tracking-widest uppercase mb-4">
                        Grade <?= htmlspecialchars($produit['grade_code']) ?>
                    </span>
                    <h1 class="text-4xl font-serif italic mb-2"><?= htmlspecialchars($produit['nom']) ?></h1>
                    <p class="text-xl font-bold uppercase tracking-widest text-gray-600">
                        <?= htmlspecialchars($produit['marque']) ?>
                    </p>
                </div>

                <!-- Prix -->
                <div class="mb-8">
                    <p class="text-5xl font-serif tracking-wider text-gold">
                        <?= number_format($produit['prix'], 0, ',', ' ') ?> €
                    </p>
                </div>

                <!-- Description du grade -->
                <div class="bg-[#F9F8F6] p-6 mb-8 rounded">
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-3 text-gold">
                        Grade <?= htmlspecialchars($produit['grade_code']) ?> : <?= htmlspecialchars($produit['grade_nom']) ?>
                    </h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        <?= htmlspecialchars($produit['grade_description']) ?>
                    </p>
                </div>

                <!-- Description du produit -->
                <?php if (!empty($produit['description'])): ?>
                <div class="mb-8">
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-4">Description</h3>
                    <p class="text-gray-600 leading-relaxed">
                        <?= nl2br(htmlspecialchars($produit['description'])) ?>
                    </p>
                </div>
                <?php endif; ?>

                <!-- Disponibilité et Stock -->
                <div class="mb-8">
                    <?php if ($produit['disponible'] && $produit['stock'] > 0): ?>
                        <div class="flex items-center space-x-3">
                            <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                            <p class="text-sm text-green-600 font-semibold">
                                Disponible immédiatement
                            </p>
                        </div>
                        <?php if ($produit['stock'] <= 3): ?>
                            <p class="text-xs text-orange-600 mt-2">
                                ⚠️ Plus que <?= $produit['stock'] ?> en stock !
                            </p>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="flex items-center space-x-3">
                            <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                            <p class="text-sm text-red-600 font-semibold">
                                Produit non disponible
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Boutons d'action -->
                <div class="space-y-4">
                    <?php if ($produit['disponible'] && $produit['stock'] > 0): ?>
                        <!-- Bouton Ajouter au Panier -->
                        <a href="<?= $base_url ?>/panier_ajouter.php?id=<?= $produit['id'] ?>&quantite=1" 
                           class="block w-full bg-black text-white text-center py-5 text-xs font-bold uppercase tracking-widest hover:bg-gold transition duration-300 rounded">
                            Ajouter au Panier
                        </a>
                        
                        <!-- Bouton Wishlist -->
                        <div class="w-full">
                            <?php 
                            $id_produit = $produit['id'];
                            $show_text = true;
                            include __DIR__ . '/../components/wishlist_button.php'; 
                            ?>
                        </div>
                        
                        <!-- Bouton Proposer un Échange -->
                        <a href="<?= $base_url ?>/echanger.php?id=<?= $produit['id'] ?>" 
                           class="block w-full border-2 border-gold text-gold text-center py-5 text-xs font-bold uppercase tracking-widest hover:bg-gold hover:text-white transition duration-300 rounded">
                            Proposer un Échange
                        </a>
                    <?php else: ?>
                        <button disabled 
                                class="w-full bg-gray-300 text-gray-500 py-5 text-xs font-bold uppercase tracking-widest cursor-not-allowed rounded">
                            Produit Indisponible
                        </button>
                        
                        <!-- Notifier quand disponible -->
                        <a href="<?= $base_url ?>/contact.php?sujet=Disponibilité+produit+<?= $produit['id'] ?>" 
                           class="block w-full border-2 border-gray-400 text-gray-600 text-center py-4 text-xs font-semibold uppercase tracking-widest hover:border-gold hover:text-gold transition duration-300 rounded">
                            Me prévenir quand disponible
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Informations complémentaires -->
                <div class="mt-12 border-t border-gray-200 pt-8">
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-6">Services Inclus</h3>
                    <ul class="space-y-4 text-sm text-gray-600">
                        <li class="flex items-start">
                            <span class="text-gold mr-3">✓</span>
                            <span>Authentification par experts certifiés</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-gold mr-3">✓</span>
                            <span>Livraison sécurisée assurée offerte</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-gold mr-3">✓</span>
                            <span>Garantie de remboursement 14 jours</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-gold mr-3">✓</span>
                            <span>Service client dédié 7j/7</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-gold mr-3">✓</span>
                            <span>Certificat d'authenticité fourni</span>
                        </li>
                    </ul>
                </div>

                <!-- Caractéristiques techniques -->
                <div class="mt-8 bg-gray-50 p-6 rounded">
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-4">Caractéristiques</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500 text-xs">Référence</p>
                            <p class="font-semibold">#<?= $produit['id'] ?></p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs">Catégorie</p>
                            <p class="font-semibold"><?= htmlspecialchars($produit['categorie_nom']) ?></p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs">État</p>
                            <p class="font-semibold">Grade <?= htmlspecialchars($produit['grade_code']) ?></p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs">Stock</p>
                            <p class="font-semibold"><?= $produit['stock'] ?> disponible<?= $produit['stock'] > 1 ? 's' : '' ?></p>
                        </div>
                    </div>
                </div>

                <!-- Contact -->
                <div class="mt-8 bg-gray-100 p-6 rounded">
                    <p class="text-xs uppercase tracking-widest font-bold mb-2">Une question sur ce produit ?</p>
                    <p class="text-sm text-gray-600 mb-4">Notre équipe d'experts est à votre disposition</p>
                    <a href="<?= $base_url ?>/contact.php?produit=<?= $produit['id'] ?>" 
                       class="text-gold text-sm font-semibold hover:underline inline-flex items-center">
                        Nous contacter 
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>

            </div>
        </div>

        <!-- Section Avis Clients -->
        <?php
        require_once __DIR__ . '/../models/Avis.php';
        
        $stats_avis = Avis::getNoteMoyenne($produit['id']);
        $avis_list = Avis::getParProduit($produit['id'], 10);
        $repartition = Avis::getRepartitionNotes($produit['id']);
        
        // Créer un tableau avec toutes les notes
        $repartition_complete = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($repartition as $r) {
            $repartition_complete[$r['note']] = $r['count'];
        }
        ?>
        
        <section class="mt-24 border-t border-gray-200 pt-24">
            <h2 class="text-3xl font-serif italic mb-12">Avis Clients</h2>
            
            <div class="grid lg:grid-cols-3 gap-12 mb-16">
                
                <!-- Résumé des notes -->
                <div class="lg:col-span-1">
                    <div class="bg-gray-50 p-8 rounded-lg text-center">
                        <div class="text-6xl font-serif text-gold mb-2">
                            <?= $stats_avis['moyenne'] ?>
                        </div>
                        <div class="flex justify-center mb-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg class="w-6 h-6 <?= $i <= round($stats_avis['moyenne']) ? 'text-gold' : 'text-gray-300' ?>" 
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            <?php endfor; ?>
                        </div>
                        <p class="text-sm text-gray-600">
                            Basé sur <?= $stats_avis['nb_avis'] ?> avis
                        </p>
                        
                        <!-- Répartition des notes -->
                        <div class="mt-6 space-y-2">
                            <?php foreach ($repartition_complete as $note => $count): ?>
                                <?php 
                                $pourcentage = $stats_avis['nb_avis'] > 0 
                                    ? round(($count / $stats_avis['nb_avis']) * 100) 
                                    : 0;
                                ?>
                                <div class="flex items-center text-sm">
                                    <span class="w-8"><?= $note ?>★</span>
                                    <div class="flex-1 h-2 bg-gray-200 rounded mx-2">
                                        <div class="h-full bg-gold rounded" style="width: <?= $pourcentage ?>%"></div>
                                    </div>
                                    <span class="w-12 text-right text-gray-500"><?= $count ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Bouton Laisser un avis -->
                        <?php if (isset($_SESSION['client_id'])): ?>
                            <?php $peut_avis = Avis::peutLaisserAvis($_SESSION['client_id'], $produit['id']); ?>
                            <?php if ($peut_avis): ?>
                                <a href="<?= $base_url ?>/avis_ajouter.php?produit=<?= $produit['id'] ?>" 
                                   class="block w-full bg-black text-white text-center py-4 text-xs font-bold uppercase tracking-widest hover:bg-gold transition mt-6 rounded">
                                    Laisser un Avis
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?= $base_url ?>/connexion.php?redirect=avis_ajouter&produit=<?= $produit['id'] ?>" 
                               class="block w-full bg-black text-white text-center py-4 text-xs font-bold uppercase tracking-widest hover:bg-gold transition mt-6 rounded">
                                Connexion pour Avis
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Liste des avis -->
                <div class="lg:col-span-2">
                    <?php if (count($avis_list) > 0): ?>
                        <div class="space-y-8">
                            <?php foreach ($avis_list as $avis): ?>
                                <?php $photos = Avis::getPhotos($avis['id']); ?>
                                <div class="border-b border-gray-200 pb-8">
                                    <!-- En-tête -->
                                    <div class="flex items-start justify-between mb-4">
                                        <div>
                                            <div class="flex items-center mb-2">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <svg class="w-5 h-5 <?= $i <= $avis['note'] ? 'text-gold' : 'text-gray-300' ?>" 
                                                         fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                <?php endfor; ?>
                                            </div>
                                            <h4 class="font-bold text-lg mb-1">
                                                <?= htmlspecialchars($avis['titre']) ?>
                                            </h4>
                                            <p class="text-sm text-gray-500">
                                                Par <?= htmlspecialchars($avis['prenom']) ?> 
                                                <?php if ($avis['verifie']): ?>
                                                    <span class="text-green-600">✓ Achat vérifié</span>
                                                <?php endif; ?>
                                                · <?= date('d/m/Y', strtotime($avis['created_at'])) ?>
                                            </p>
                                        </div>
                                        
                                        <?php if ($avis['recommande']): ?>
                                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                                ✓ Recommande
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Commentaire -->
                                    <p class="text-gray-600 leading-relaxed mb-4">
                                        <?= nl2br(htmlspecialchars($avis['commentaire'])) ?>
                                    </p>
                                    
                                    <!-- Photos -->
                                    <?php if (count($photos) > 0): ?>
                                        <div class="flex gap-2 mb-4">
                                            <?php foreach ($photos as $photo): ?>
                                                <img src="<?= $base_url ?>/public/images/avis/<?= $photo['chemin'] ?>" 
                                                     class="w-20 h-20 object-cover rounded cursor-pointer hover:opacity-75 transition"
                                                     onclick="window.open(this.src)"
                                                     alt="Photo avis">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Actions -->
                                    <div class="flex items-center space-x-4 text-sm">
                                        <button class="text-gray-500 hover:text-gold transition">
                                            👍 Utile (<?= $avis['votes_utiles'] ?? 0 ?>)
                                        </button>
                                        <button class="text-gray-500 hover:text-gold transition">
                                            👎 (<?= $avis['votes_inutiles'] ?? 0 ?>)
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <p class="text-gray-500 mb-4">Aucun avis pour le moment</p>
                            <p class="text-sm text-gray-400">Soyez le premier à partager votre expérience !</p>
                        </div>
                    <?php endif; ?>
                </div>
                
            </div>
        </section>

        <!-- Produits similaires -->
        <?php
        $produits_similaires = Produit::getAll($produit['id_categorie'], null, 4);
        if (count($produits_similaires) > 0):
        ?>
        <section class="mt-24">
            <h2 class="text-3xl font-serif italic mb-12 text-center">Vous pourriez aussi aimer</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                <?php foreach ($produits_similaires as $p): ?>
                    <?php if ($p['id'] != $produit['id']): ?>
                    <div class="group cursor-pointer">
                        <a href="<?= $base_url ?>/views/produit_detail.php?id=<?= $p['id'] ?>">
                            <div class="relative overflow-hidden aspect-[4/5] bg-gray-100 mb-6 rounded">
                                <?php 
                                $img = !empty($p['image_principale']) 
                                    ? $base_url . '/public/images/produits/' . $p['image_principale']
                                    : 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?q=80&w=1957&auto=format&fit=crop';
                                ?>
                                <img src="<?= $img ?>" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-700"
                                     alt="<?= htmlspecialchars($p['nom']) ?>">
                                <div class="absolute top-4 left-4">
                                    <span class="bg-black text-white px-3 py-1 text-[9px] font-bold tracking-widest uppercase">
                                        Grade <?= htmlspecialchars($p['grade_code']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-xs uppercase tracking-widest mb-1">
                                        <?= htmlspecialchars($p['marque']) ?>
                                    </h4>
                                    <p class="text-gray-500 font-light text-sm italic">
                                        <?= htmlspecialchars($p['nom']) ?>
                                    </p>
                                </div>
                                <span class="font-serif text-lg tracking-wider text-gold">
                                    <?= number_format($p['prix'], 0, ',', ' ') ?> €
                                </span>
                            </div>
                        </a>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

    </div>
</main>

<script>
    // Fonction pour changer l'image principale
    function changerImage(url) {
        document.getElementById('imagePrincipale').src = url;
        
        // Mettre à jour les bordures des miniatures
        document.querySelectorAll('[onclick^="changerImage"]').forEach(img => {
            img.classList.remove('border-gold');
            img.classList.add('border-gray-200');
        });
        event.target.classList.remove('border-gray-200');
        event.target.classList.add('border-gold');
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>