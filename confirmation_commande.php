<?php 
$page_title = "Commande Confirmée | DIAMON";
require_once __DIR__ . '/includes/header.php';

$numero_commande = $_GET['numero'] ?? null;

if (!$numero_commande) {
    header('Location: index.php');
    exit();
}
?>

<main class="pt-32 pb-24">
    <div class="max-w-4xl mx-auto px-6 text-center">
        
        <div class="mb-12">
            <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-8">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h1 class="text-5xl font-serif italic mb-4">Commande Confirmée !</h1>
            <p class="text-gray-500 text-lg mb-8">
                Merci pour votre confiance
            </p>
            
            <div class="bg-[#F9F8F6] p-8 rounded-lg inline-block">
                <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 mb-2">Numéro de commande</p>
                <p class="text-3xl font-serif text-gold">#<?= htmlspecialchars($numero_commande) ?></p>
            </div>
        </div>

        <div class="max-w-2xl mx-auto mb-12">
            <div class="bg-white border border-gray-200 p-8 rounded-lg text-left">
                <h2 class="text-xl font-serif italic mb-6 text-center">Que va-t-il se passer maintenant ?</h2>
                
                <div class="space-y-6">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-gold text-white rounded-full flex items-center justify-center font-serif text-xl">
                            1
                        </div>
                        <div>
                            <h3 class="font-bold text-sm uppercase tracking-widest mb-2">Confirmation par Email</h3>
                            <p class="text-sm text-gray-600">Vous allez recevoir un email de confirmation avec tous les détails de votre commande.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-gold text-white rounded-full flex items-center justify-center font-serif text-xl">
                            2
                        </div>
                        <div>
                            <h3 class="font-bold text-sm uppercase tracking-widest mb-2">Préparation</h3>
                            <p class="text-sm text-gray-600">Nos experts vérifient une dernière fois l'authenticité et l'état de vos articles.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-gold text-white rounded-full flex items-center justify-center font-serif text-xl">
                            3
                        </div>
                        <div>
                            <h3 class="font-bold text-sm uppercase tracking-widest mb-2">Expédition Sécurisée</h3>
                            <p class="text-sm text-gray-600">Votre commande est emballée avec soin et expédiée via transporteur premium assuré.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-gold text-white rounded-full flex items-center justify-center font-serif text-xl">
                            4
                        </div>
                        <div>
                            <h3 class="font-bold text-sm uppercase tracking-widest mb-2">Livraison</h3>
                            <p class="text-sm text-gray-600">Réception de votre pièce de luxe certifiée sous 2 à 5 jours ouvrés.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-4 justify-center">
            <a href="<?= $base_url ?>/compte.php" 
               class="bg-black text-white px-12 py-5 text-xs font-bold uppercase tracking-widest hover:bg-gold transition">
                Voir mes Commandes
            </a>
            <a href="<?= $base_url ?>/catalogue.php" 
               class="border-2 border-black text-black px-12 py-5 text-xs font-bold uppercase tracking-widest hover:bg-black hover:text-white transition">
                Continuer mes Achats
            </a>
        </div>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>