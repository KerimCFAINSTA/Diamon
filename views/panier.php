<?php 
$page_title = "Mon Panier | DIAMON";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Panier.php';

// Rediriger si non connecté
if (!isset($_SESSION['client_id'])) {
    header('Location: ' . $base_url . '/connexion.php?redirect=panier');
    exit();
}

$id_client = $_SESSION['client_id'];

// Récupérer le contenu complet du panier
$panier = Panier::getContenuComplet($id_client);
?>

<main class="pt-32 pb-24">
    <div class="max-w-7xl mx-auto px-6">
        
        <div class="text-center mb-16">
            <h1 class="text-5xl font-serif italic mb-4">Mon Panier</h1>
            <p class="text-gray-500 text-sm"><?= $panier['nb_articles'] ?> article<?= $panier['nb_articles'] > 1 ? 's' : '' ?></p>
        </div>

        <?php if (isset($_SESSION['panier_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded mb-8">
                ✓ <?= htmlspecialchars($_SESSION['panier_message']) ?>
            </div>
            <?php unset($_SESSION['panier_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['panier_erreur'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded mb-8">
                ✗ <?= htmlspecialchars($_SESSION['panier_erreur']) ?>
            </div>
            <?php unset($_SESSION['panier_erreur']); ?>
        <?php endif; ?>

        <?php if (count($panier['articles']) > 0): ?>
            <div class="grid lg:grid-cols-3 gap-12">
                
                <!-- Liste des produits -->
                <div class="lg:col-span-2 space-y-6">
                    <?php foreach ($panier['articles'] as $article): ?>
                        <div class="flex gap-6 border-b border-gray-200 pb-6">
                            
                            <!-- Image -->
                            <div class="w-32 h-32 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                                <?php if (!empty($article['image_principale'])): ?>
                                    <img src="<?= $base_url ?>/public/images/produits/<?= $article['image_principale'] ?>" 
                                         alt="<?= htmlspecialchars($article['nom']) ?>"
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <img src="https://images.unsplash.com/photo-1594223274512-ad4803739b7c?q=80&w=400" 
                                         alt="<?= htmlspecialchars($article['nom']) ?>"
                                         class="w-full h-full object-cover">
                                <?php endif; ?>
                            </div>
                            
                            <!-- Détails -->
                            <div class="flex-1">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="text-xs uppercase tracking-widest font-bold mb-1">
                                            <?= htmlspecialchars($article['marque']) ?>
                                        </h3>
                                        <p class="text-sm text-gray-600 mb-2">
                                            <?= htmlspecialchars($article['nom']) ?>
                                        </p>
                                    </div>
                                    
                                    <!-- Bouton Retirer -->
                                    <a href="<?= $base_url ?>/panier_retirer.php?id=<?= $article['id_produit'] ?>" 
                                       class="text-red-500 hover:text-red-700 text-xs uppercase tracking-widest font-semibold"
                                       onclick="return confirm('Retirer cet article du panier ?')">
                                        Retirer
                                    </a>
                                </div>

                                <div class="flex items-center justify-between mt-4">
                                    <!-- Quantité -->
                                    <div class="flex items-center space-x-3">
                                        <a href="<?= $base_url ?>/panier_quantite.php?id=<?= $article['id_produit'] ?>&action=diminuer" 
                                           class="w-8 h-8 border-2 border-gray-300 flex items-center justify-center hover:bg-gray-100 transition text-lg">
                                            −
                                        </a>
                                        <span class="font-semibold text-lg w-8 text-center"><?= $article['quantite'] ?></span>
                                        <a href="<?= $base_url ?>/panier_quantite.php?id=<?= $article['id_produit'] ?>&action=augmenter" 
                                           class="w-8 h-8 border-2 border-gray-300 flex items-center justify-center hover:bg-gray-100 transition text-lg">
                                            +
                                        </a>
                                    </div>

                                    <!-- Prix -->
                                    <div class="text-right">
                                        <p class="text-xl font-serif text-gold">
                                            <?= number_format($article['prix'] * $article['quantite'], 0, ',', ' ') ?> €
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?= number_format($article['prix'], 0, ',', ' ') ?> € / unité
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Récapitulatif -->
                <div class="lg:col-span-1">
                    <div class="bg-[#F9F8F6] p-8 sticky top-32">
                        <h2 class="text-xs uppercase tracking-widest font-bold mb-6">Récapitulatif</h2>
                        
                        <!-- Code Promo -->
                        <div class="mb-6 pb-6 border-b border-gray-300">
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-3">
                                Code Promo
                            </label>
                            
                            <?php if ($panier['code_promo']): ?>
                                <!-- Code promo appliqué -->
                                <div class="bg-green-50 border-2 border-green-500 rounded p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-bold font-mono text-green-800 text-sm">
                                            <?= htmlspecialchars($panier['code_promo']['code']) ?>
                                        </span>
                                        <button onclick="retirerCodePromo()" 
                                                class="text-red-500 hover:text-red-700 text-xs font-bold">
                                            ✕ Retirer
                                        </button>
                                    </div>
                                    <p class="text-xs text-green-700">
                                        <?php if ($panier['code_promo']['type'] === 'pourcentage'): ?>
                                            Réduction de <?= $panier['code_promo']['valeur'] ?>%
                                        <?php else: ?>
                                            Réduction de <?= number_format($panier['code_promo']['valeur'], 0, ',', ' ') ?> €
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php else: ?>
                                <!-- Formulaire d'application -->
                                <form id="formCodePromo" class="space-y-2">
                                    <div class="flex gap-2">
                                        <input type="text" 
                                               id="inputCodePromo"
                                               name="code" 
                                               placeholder="CODE PROMO"
                                               class="flex-1 px-4 py-3 border border-gray-300 rounded uppercase font-mono text-sm focus:outline-none focus:border-gold">
                                        <button type="submit"
                                                class="px-6 py-3 bg-black text-white text-xs font-bold uppercase tracking-widest hover:bg-gold transition rounded">
                                            OK
                                        </button>
                                    </div>
                                    <div id="messageCodePromo" class="text-sm"></div>
                                </form>
                            <?php endif; ?>
                        </div>

                        <!-- Sous-total et Réduction -->
                        <div class="space-y-4 mb-6 pb-6 border-b border-gray-300">
                            <div class="flex justify-between text-sm">
                                <span>Sous-total</span>
                                <span id="sousTotal"><?= number_format($panier['sous_total'], 2, ',', ' ') ?> €</span>
                            </div>
                            
                            <?php if ($panier['reduction'] > 0): ?>
                                <div class="flex justify-between text-sm text-green-600">
                                    <span>Réduction</span>
                                    <span class="font-semibold" id="reduction">
                                        -<?= number_format($panier['reduction'], 2, ',', ' ') ?> €
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex justify-between text-sm">
                                <span>Livraison</span>
                                <span class="text-green-600 font-semibold">Offerte</span>
                            </div>
                        </div>
                        
                        <!-- Total -->
                        <div class="flex justify-between text-xl font-serif mb-8">
                            <span>Total</span>
                            <span class="text-gold" id="total"><?= number_format($panier['total'], 2, ',', ' ') ?> €</span>
                        </div>

                        <!-- Bouton Commander -->
                        <a href="<?= $base_url ?>/commande.php" 
                           class="block w-full bg-black text-white text-center py-5 text-xs font-bold uppercase tracking-widest hover:bg-gold transition duration-300">
                            Commander
                        </a>

                        <a href="<?= $base_url ?>/catalogue.php" 
                           class="block w-full border-2 border-black text-black text-center py-5 text-xs font-bold uppercase tracking-widest hover:bg-black hover:text-white transition duration-300 mt-4">
                            Continuer mes Achats
                        </a>

                        <!-- Services -->
                        <div class="mt-8 pt-8 border-t border-gray-300">
                            <ul class="space-y-3 text-xs text-gray-600">
                                <li class="flex items-start">
                                    <span class="text-gold mr-2">✓</span>
                                    <span>Livraison sécurisée offerte</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-gold mr-2">✓</span>
                                    <span>Paiement 100% sécurisé</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-gold mr-2">✓</span>
                                    <span>Garantie authenticité</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-gold mr-2">✓</span>
                                    <span>Retour sous 14 jours</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        <?php else: ?>
            <!-- Panier vide -->
            <div class="text-center py-20">
                <div class="text-6xl mb-6">🛒</div>
                <p class="text-gray-500 text-lg mb-8">Votre panier est vide</p>
                <a href="<?= $base_url ?>/catalogue.php" 
                   class="inline-block bg-black text-white px-12 py-5 text-xs font-bold uppercase tracking-widest hover:bg-gold transition duration-300">
                    Découvrir notre Collection
                </a>
            </div>
        <?php endif; ?>

    </div>
</main>

<script>
    // Appliquer un code promo
    document.getElementById('formCodePromo')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const code = document.getElementById('inputCodePromo').value.trim();
        const messageDiv = document.getElementById('messageCodePromo');
        const submitBtn = e.target.querySelector('button[type="submit"]');
        
        if (!code) {
            messageDiv.innerHTML = '<p class="text-red-600">Veuillez entrer un code promo</p>';
            return;
        }
        
        // Désactiver le bouton pendant le chargement
        submitBtn.disabled = true;
        submitBtn.textContent = '...';
        messageDiv.innerHTML = '<p class="text-gray-500">Vérification...</p>';
        
        try {
            const response = await fetch('<?= $base_url ?>/panier_code_promo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=appliquer&code=' + encodeURIComponent(code)
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Recharger la page pour afficher les changements
                location.reload();
            } else {
                messageDiv.innerHTML = '<p class="text-red-600">✗ ' + data.message + '</p>';
                submitBtn.disabled = false;
                submitBtn.textContent = 'OK';
            }
            
        } catch (error) {
            messageDiv.innerHTML = '<p class="text-red-600">Erreur de connexion</p>';
            submitBtn.disabled = false;
            submitBtn.textContent = 'OK';
        }
    });
    
    // Retirer un code promo
    async function retirerCodePromo() {
        if (!confirm('Retirer le code promo ?')) return;
        
        try {
            const response = await fetch('<?= $base_url ?>/panier_code_promo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=retirer'
            });
            
            const data = await response.json();
            
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur : ' + data.message);
            }
            
        } catch (error) {
            alert('Erreur de connexion');
        }
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>