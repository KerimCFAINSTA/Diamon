<?php 
$page_title = "Finaliser la Commande | DIAMON";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/models/Produit.php';
require_once __DIR__ . '/models/Commande.php';
require_once __DIR__ . '/models/Client.php';

// Vérifier la connexion
if (!isset($_SESSION['client_id'])) {
    header('Location: connexion.php?redirect=panier');
    exit();
}

// Vérifier le panier
if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
    header('Location: views/panier.php');
    exit();
}

$client = Client::getById($_SESSION['client_id']);
$produits_panier = [];
$total = 0;

foreach ($_SESSION['panier'] as $id_produit => $quantite) {
    $produit = Produit::getById($id_produit);
    if ($produit && $produit['disponible']) {
        $produit['quantite'] = $quantite;
        $produits_panier[] = $produit;
        $total += $produit['prix'] * $quantite;
    }
}

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adresse = trim($_POST['adresse'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $code_postal = trim($_POST['code_postal'] ?? '');
    $pays = trim($_POST['pays'] ?? 'France');
    
    // Validation
    if (empty($adresse)) $erreurs[] = "L'adresse est requise.";
    if (empty($ville)) $erreurs[] = "La ville est requise.";
    if (empty($code_postal)) $erreurs[] = "Le code postal est requis.";
    
    if (empty($erreurs)) {
        $adresse_livraison = $adresse . "\n" . $code_postal . " " . $ville . "\n" . $pays;
        
        $resultat = Commande::creer($_SESSION['client_id'], $produits_panier, $adresse_livraison);
        
        if ($resultat['success']) {
            // Vider le panier
            $_SESSION['panier'] = [];
            $_SESSION['panier_count'] = 0;
            
            // Redirection vers la confirmation
            header('Location: confirmation_commande.php?numero=' . $resultat['numero_commande']);
            exit();
        } else {
            $erreurs[] = "Une erreur est survenue lors de la création de la commande.";
        }
    }
}
?>

<main class="pt-32 pb-24">
    <div class="max-w-7xl mx-auto px-6">
        
        <div class="text-center mb-16">
            <h1 class="text-5xl font-serif italic mb-4">Finaliser ma Commande</h1>
        </div>

        <?php if (!empty($erreurs)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded mb-8">
                <ul class="list-disc list-inside">
                    <?php foreach ($erreurs as $erreur): ?>
                        <li><?= htmlspecialchars($erreur) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="grid lg:grid-cols-3 gap-12">
            
            <!-- Formulaire -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Informations client -->
                <div>
                    <h2 class="text-xl font-serif italic mb-6">Vos Informations</h2>
                    <div class="bg-[#F9F8F6] p-6 rounded">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">Nom</p>
                                <p><?= htmlspecialchars($client['nom']) ?></p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">Prénom</p>
                                <p><?= htmlspecialchars($client['prenom']) ?></p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">Email</p>
                                <p><?= htmlspecialchars($client['email']) ?></p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-1">Téléphone</p>
                                <p><?= htmlspecialchars($client['telephone'] ?? 'Non renseigné') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Adresse de livraison -->
                <div>
                    <h2 class="text-xl font-serif italic mb-6">Adresse de Livraison</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Adresse *</label>
                            <input type="text" 
                                   name="adresse" 
                                   value="<?= htmlspecialchars($_POST['adresse'] ?? $client['adresse'] ?? '') ?>"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Code Postal *</label>
                                <input type="text" 
                                       name="code_postal" 
                                       value="<?= htmlspecialchars($_POST['code_postal'] ?? $client['code_postal'] ?? '') ?>"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Ville *</label>
                                <input type="text" 
                                       name="ville" 
                                       value="<?= htmlspecialchars($_POST['ville'] ?? $client['ville'] ?? '') ?>"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Pays *</label>
                            <input type="text" 
                                   name="pays" 
                                   value="<?= htmlspecialchars($_POST['pays'] ?? $client['pays'] ?? 'France') ?>"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Récapitulatif -->
            <div class="lg:col-span-1">
                <div class="bg-[#F9F8F6] p-8 sticky top-32">
                    <h2 class="text-xs uppercase tracking-widest font-bold mb-6">Récapitulatif</h2>
                    
                    <!-- Produits -->
                    <div class="space-y-4 mb-6 pb-6 border-b border-gray-300">
                        <?php foreach ($produits_panier as $produit): ?>
                            <div class="flex gap-3">
                                <img src="<?= $base_url ?>/public/images/produits/<?= $produit['image_principale'] ?>" 
                                     alt="<?= htmlspecialchars($produit['nom']) ?>"
                                     class="w-16 h-16 object-cover">
                                <div class="flex-1">
                                    <p class="text-xs font-semibold"><?= htmlspecialchars($produit['marque']) ?></p>
                                    <p class="text-xs text-gray-600"><?= htmlspecialchars($produit['nom']) ?></p>
                                    <p class="text-sm font-serif"><?= number_format($produit['prix'], 0, ',', ' ') ?> €</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="space-y-4 mb-6 pb-6 border-b border-gray-300">
                        <div class="flex justify-between text-sm">
                            <span>Sous-total</span>
                            <span><?= number_format($total, 0, ',', ' ') ?> €</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Livraison</span>
                            <span class="text-green-600 font-semibold">Offerte</span>
                        </div>
                    </div>
                    
                    <div class="flex justify-between text-xl font-serif mb-8">
                        <span>Total</span>
                        <span class="text-gold"><?= number_format($total, 0, ',', ' ') ?> €</span>
                    </div>

                    <button type="submit" 
                            class="w-full bg-black text-white py-5 text-xs font-bold uppercase tracking-widest hover:bg-gold transition duration-300">
                        Confirmer la Commande
                    </button>

                    <p class="text-xs text-gray-500 mt-6 text-center">
                        En validant votre commande, vous acceptez nos conditions générales de vente
                    </p>
                </div>
            </div>

        </form>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>