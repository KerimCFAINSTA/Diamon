<?php 
$page_title = "Vendre votre Pièce de Luxe | DIAMON";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/models/DemandeVente.php';
require_once __DIR__ . '/models/Categorie.php';

$categories = Categorie::getAll();
$erreurs = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    $marque = trim($_POST['marque'] ?? '');
    $nom_produit = trim($_POST['nom_produit'] ?? '');
    $categorie = $_POST['categorie'] ?? '';
    $etat_estime = $_POST['etat_estime'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $prix_souhaite = $_POST['prix_souhaite'] ?? null;
    $annee_achat = $_POST['annee_achat'] ?? null;
    $avec_boite = isset($_POST['avec_boite']) ? 1 : 0;
    $avec_certificat = isset($_POST['avec_certificat']) ? 1 : 0;
    $avec_accessoires = isset($_POST['avec_accessoires']) ? 1 : 0;
    
    if (empty($marque)) $erreurs[] = "La marque est requise.";
    if (empty($nom_produit)) $erreurs[] = "Le nom du produit est requis.";
    if (empty($categorie)) $erreurs[] = "La catégorie est requise.";
    if (empty($etat_estime)) $erreurs[] = "L'état estimé est requis.";
    if (empty($description)) $erreurs[] = "La description est requise.";
    
    // Gestion des photos (upload)
    $photos = [];
    for ($i = 1; $i <= 4; $i++) {
        if (isset($_FILES["photo_$i"]) && $_FILES["photo_$i"]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES["photo_$i"];
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'vente_' . uniqid() . '.' . $extension;
            $destination = __DIR__ . '/public/images/ventes/' . $filename;
            
            // Créer le dossier s'il n'existe pas
            if (!is_dir(__DIR__ . '/public/images/ventes/')) {
                mkdir(__DIR__ . '/public/images/ventes/', 0777, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $photos["photo_$i"] = $filename;
            }
        } else {
            $photos["photo_$i"] = null;
        }
    }
    
    if (empty($erreurs)) {
        // Si l'utilisateur n'est pas connecté, rediriger vers inscription
        if (!isset($_SESSION['client_id'])) {
            $_SESSION['vente_data'] = array_merge($_POST, $photos);
            header('Location: inscription.php?redirect=vendre');
            exit();
        }
        
        $data = [
            'id_client' => $_SESSION['client_id'],
            'marque' => $marque,
            'nom_produit' => $nom_produit,
            'categorie' => $categorie,
            'etat_estime' => $etat_estime,
            'description' => $description,
            'prix_souhaite' => $prix_souhaite,
            'annee_achat' => $annee_achat,
            'avec_boite' => $avec_boite,
            'avec_certificat' => $avec_certificat,
            'avec_accessoires' => $avec_accessoires,
            'photo_1' => $photos['photo_1'],
            'photo_2' => $photos['photo_2'],
            'photo_3' => $photos['photo_3'],
            'photo_4' => $photos['photo_4']
        ];
        
        if (DemandeVente::creer($data)) {
            $success = true;
        } else {
            $erreurs[] = "Une erreur est survenue lors de l'envoi de votre demande.";
        }
    }
    if (DemandeVente::creer($data)) {
    $success = true;
    
    // 📧 Envoyer email de confirmation au client
    require_once __DIR__ . '/includes/email_config.php';
    EmailService::notifierStatutVente(
        $_SESSION['client_email'] ?? $email,
        $prenom . ' ' . $nom,
        $pdo->lastInsertId(),
        'en attente',
        null,
        'Votre demande a bien été reçue. Nos experts vont l\'examiner sous 48h.'
    );
} else {
    $erreurs[] = "Une erreur est survenue lors de l'envoi de votre demande.";
}
}
?>

<main class="pt-32 pb-24">
    <div class="max-w-7xl mx-auto px-6">
        
        <!-- Hero Section -->
        <div class="text-center mb-16">
            <h1 class="text-5xl md:text-6xl font-serif italic mb-6">Vendez votre Pièce de Luxe</h1>
            <p class="text-gray-600 text-lg max-w-3xl mx-auto">
                DIAMON vous accompagne dans la vente de vos articles de luxe. 
                Nos experts certifient l'authenticité et vous garantissent le meilleur prix du marché.
            </p>
        </div>

        <?php if ($success): ?>
            <div class="max-w-2xl mx-auto mb-12">
                <div class="bg-green-100 border-2 border-green-400 text-green-800 p-8 rounded-lg text-center">
                    <div class="w-16 h-16 bg-green-200 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-serif italic mb-3">Demande Envoyée !</h2>
                    <p class="mb-6">
                        Votre demande de vente a été transmise à nos experts. 
                        Vous recevrez une estimation sous 48h ouvrées.
                    </p>
                    <a href="<?= $base_url ?>/compte.php" 
                       class="inline-block bg-green-600 text-white px-8 py-3 text-xs font-bold uppercase tracking-widest hover:bg-green-700 transition">
                        Voir mes Demandes
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Processus -->
        <div class="bg-[#F9F8F6] py-16 mb-16 -mx-6 px-6">
            <h2 class="text-3xl font-serif italic text-center mb-12">Comment ça marche ?</h2>
            <div class="max-w-5xl mx-auto grid md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gold text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-serif">1</div>
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-2">Remplissez le Formulaire</h3>
                    <p class="text-sm text-gray-600">Décrivez votre article en détail avec photos</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-gold text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-serif">2</div>
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-2">Expertise Gratuite</h3>
                    <p class="text-sm text-gray-600">Nos experts évaluent votre pièce sous 48h</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-gold text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-serif">3</div>
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-2">Acceptez l'Offre</h3>
                    <p class="text-sm text-gray-600">Vous validez ou négociez notre proposition</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-gold text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-serif">4</div>
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-2">Recevez votre Paiement</h3>
                    <p class="text-sm text-gray-600">Paiement sécurisé sous 72h après réception</p>
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <?php if (!$success): ?>
        <div class="max-w-4xl mx-auto">
            
            <?php if (!empty($erreurs)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded mb-8">
                    <ul class="list-disc list-inside">
                        <?php foreach ($erreurs as $erreur): ?>
                            <li><?= htmlspecialchars($erreur) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="space-y-8">
                
                <!-- Informations Produit -->
                <div class="bg-white border border-gray-200 p-8 rounded-lg">
                    <h2 class="text-2xl font-serif italic mb-6">Informations sur votre Article</h2>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Marque *</label>
                            <input type="text" 
                                   name="marque" 
                                   value="<?= htmlspecialchars($_POST['marque'] ?? '') ?>"
                                   placeholder="Ex: Hermès, Rolex, Chanel..."
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Nom du Produit *</label>
                            <input type="text" 
                                   name="nom_produit" 
                                   value="<?= htmlspecialchars($_POST['nom_produit'] ?? '') ?>"
                                   placeholder="Ex: Sac Birkin 35, Submariner Date..."
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Catégorie *</label>
                            <select name="categorie" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                                <option value="">-- Choisir --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['slug'] ?>" <?= (isset($_POST['categorie']) && $_POST['categorie'] == $cat['slug']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">État Estimé *</label>
                            <select name="etat_estime" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                                <option value="">-- Choisir --</option>
                                <option value="A++">A++ - État Boutique (Neuf)</option>
                                <option value="A+">A+ - État Exceptionnel</option>
                                <option value="A">A - Excellent État</option>
                                <option value="B">B - Très Bon État</option>
                                <option value="C">C - Bon État</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Année d'Achat</label>
                            <input type="number" 
                                   name="annee_achat" 
                                   value="<?= htmlspecialchars($_POST['annee_achat'] ?? '') ?>"
                                   min="1900" 
                                   max="<?= date('Y') ?>"
                                   placeholder="2020"
                                   class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Prix Souhaité (€)</label>
                            <input type="number" 
                                   name="prix_souhaite" 
                                   value="<?= htmlspecialchars($_POST['prix_souhaite'] ?? '') ?>"
                                   min="0"
                                   step="0.01"
                                   placeholder="5000"
                                   class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                            <p class="text-xs text-gray-500 mt-1">Optionnel - Nos experts vous feront une estimation</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Description Détaillée *</label>
                        <textarea name="description" 
                                  rows="6"
                                  required
                                  placeholder="Décrivez votre article : origine, histoire, état général, éventuels défauts..."
                                  class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mt-6 space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="avec_boite" class="mr-3" <?= isset($_POST['avec_boite']) ? 'checked' : '' ?>>
                            <span class="text-sm">J'ai la boîte d'origine</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="avec_certificat" class="mr-3" <?= isset($_POST['avec_certificat']) ? 'checked' : '' ?>>
                            <span class="text-sm">J'ai le certificat d'authenticité / Facture</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="avec_accessoires" class="mr-3" <?= isset($_POST['avec_accessoires']) ? 'checked' : '' ?>>
                            <span class="text-sm">J'ai les accessoires d'origine (dustbag, papiers, etc.)</span>
                        </label>
                    </div>
                </div>

                <!-- Photos -->
                <div class="bg-white border border-gray-200 p-8 rounded-lg">
                    <h2 class="text-2xl font-serif italic mb-6">Photos de votre Article</h2>
                    <p class="text-sm text-gray-600 mb-6">
                        Ajoutez au moins 3 photos de bonne qualité (face, dos, détails, défauts éventuels)
                    </p>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Photo 1 *</label>
                            <input type="file" 
                                   name="photo_1" 
                                   accept="image/*"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Photo 2 *</label>
                            <input type="file" 
                                   name="photo_2" 
                                   accept="image/*"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Photo 3 *</label>
                            <input type="file" 
                                   name="photo_3" 
                                   accept="image/*"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Photo 4 (optionnelle)</label>
                            <input type="file" 
                                   name="photo_4" 
                                   accept="image/*"
                                   class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="text-center">
                    <button type="submit" 
                            class="bg-black text-white px-16 py-5 text-xs font-bold uppercase tracking-widest hover:bg-gold transition duration-300">
                        Envoyer ma Demande
                    </button>
                    <p class="text-xs text-gray-500 mt-4">
                        Expertise gratuite et sans engagement
                    </p>
                </div>

            </form>
        </div>
        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>