<?php 
$page_title = "Mon Compte | DIAMON";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/models/Client.php';
require_once __DIR__ . '/models/Commande.php';

// Vérifier la connexion
if (!isset($_SESSION['client_id'])) {
    header('Location: connexion.php');
    exit();
}

$client = Client::getById($_SESSION['client_id']);
$commandes = Commande::getByClient($_SESSION['client_id']);
?>

<main class="pt-32 pb-24">
    <div class="max-w-7xl mx-auto px-6">
        
        <div class="text-center mb-16">
            <h1 class="text-5xl font-serif italic mb-4">Mon Compte</h1>
            <p class="text-gray-500 text-sm">
                Bienvenue <?= htmlspecialchars($client['prenom']) ?> <?= htmlspecialchars($client['nom']) ?>
            </p>
        </div>

        <div class="grid lg:grid-cols-4 gap-12">
            
            <!-- Menu latéral -->
            <div class="lg:col-span-1">
                <nav class="space-y-2">
                    <a href="#informations" class="block px-6 py-3 bg-black text-white text-xs uppercase tracking-widest font-semibold">
                        Mes Informations
                    </a>
                    <a href="#commandes" class="block px-6 py-3 border border-gray-300 hover:bg-gray-100 text-xs uppercase tracking-widest font-semibold">
                        Mes Commandes
                    </a>
                    <a href="<?= $base_url ?>/deconnexion.php" class="block px-6 py-3 border border-gray-300 hover:bg-red-100 hover:border-red-300 text-xs uppercase tracking-widest font-semibold text-red-600">
                        Déconnexion
                    </a>
                </nav>
            </div>

            <!-- Contenu -->
            <div class="lg:col-span-3">
                
                <!-- Informations personnelles -->
                <section id="informations" class="mb-16">
                    <h2 class="text-2xl font-serif italic mb-8">Mes Informations</h2>
                    <div class="bg-[#F9F8F6] p-8 rounded">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-2">Nom</p>
                                <p class="text-lg"><?= htmlspecialchars($client['nom']) ?></p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-2">Prénom</p>
                                <p class="text-lg"><?= htmlspecialchars($client['prenom']) ?></p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-2">Email</p>
                                <p class="text-lg"><?= htmlspecialchars($client['email']) ?></p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-2">Téléphone</p>
                                <p class="text-lg"><?= htmlspecialchars($client['telephone'] ?? 'Non renseigné') ?></p>
                            </div>
                        </div>
                        <div class="mt-6">
                            <button class="text-xs uppercase tracking-widest font-semibold text-gold hover:underline">
                                Modifier mes informations
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Commandes -->
                <section id="commandes">
                    <h2 class="text-2xl font-serif italic mb-8">Mes Commandes</h2>
                    
                    <?php if (count($commandes) > 0): ?>
                        <div class="space-y-6">
                            <?php foreach ($commandes as $commande): ?>
                                <?php $details = Commande::getDetails($commande['id']); ?>
                                <div class="border border-gray-300 p-6 hover:border-gold transition">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <p class="text-xs uppercase tracking-widest font-semibold text-gray-500">
                                                Commande #<?= htmlspecialchars($commande['numero_commande']) ?>
                                            </p>
                                            <p class="text-sm text-gray-600 mt-1">
                                                <?= date('d/m/Y à H:i', strtotime($commande['created_at'])) ?>
                                            </p>
                                        </div>
                                        <div>
                                            <?php
                                            $statut_colors = [
                                                'en attente' => 'bg-yellow-100 text-yellow-800',
                                                'payée' => 'bg-green-100 text-green-800',
                                                'expédiée' => 'bg-blue-100 text-blue-800',
                                                'livrée' => 'bg-gray-100 text-gray-800',
                                                'annulée' => 'bg-red-100 text-red-800'
                                            ];
                                            $color = $statut_colors[$commande['statut']] ?? 'bg-gray-100 text-gray-800';
                                            ?>
                                            <span class="<?= $color ?> px-3 py-1 text-[9px] font-bold uppercase tracking-widest">
                                                <?= htmlspecialchars($commande['statut']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="border-t border-gray-200 pt-4 mt-4">
                                        <?php foreach ($details as $detail): ?>
                                            <div class="flex items-center gap-4 mb-3">
                                                <img src="<?= $base_url ?>/public/images/produits/<?= $detail['image_principale'] ?>" 
                                                     alt="<?= htmlspecialchars($detail['nom']) ?>"
                                                     class="w-16 h-16 object-cover">
                                                <div class="flex-1">
                                                    <p class="text-xs uppercase tracking-widest font-semibold">
                                                        <?= htmlspecialchars($detail['marque']) ?>
                                                    </p>
                                                    <p class="text-sm text-gray-600">
                                                        <?= htmlspecialchars($detail['nom']) ?>
                                                    </p>
                                                </div>
                                                <p class="font-serif text-lg">
                                                    <?= number_format($detail['prix_unitaire'], 0, ',', ' ') ?> €
                                                </p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="border-t border-gray-200 pt-4 mt-4 flex justify-between items-center">
                                        <p class="text-xs uppercase tracking-widest font-semibold">Total</p>
                                        <p class="text-2xl font-serif text-gold">
                                            <?= number_format($commande['montant_total'], 0, ',', ' ') ?> €
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 bg-gray-50">
                            <p class="text-gray-500 mb-6">Vous n'avez pas encore passé de commande</p>
                            <a href="<?= $base_url ?>/catalogue.php" 
                               class="inline-block bg-black text-white px-8 py-4 text-xs font-bold uppercase tracking-widest hover:bg-gold transition">
                                Découvrir notre Collection
                            </a>
                        </div>
                    <?php endif; ?>
                </section>

            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>