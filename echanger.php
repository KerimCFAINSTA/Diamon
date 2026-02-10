<?php 
$page_title = "Échanger votre Pièce | DIAMON";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/models/DemandeEchange.php';
require_once __DIR__ . '/models/Produit.php';
require_once __DIR__ . '/models/Categorie.php';

// Récupérer le produit souhaité si spécifié
$id_produit_souhaite = $_GET['id'] ?? null;
$produit_souhaite = null;

if ($id_produit_souhaite) {
    $produit_souhaite = Produit::getById($id_produit_souhaite);
}

$categories = Categorie::getAll();
$erreurs = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    $id_produit_souhaite = $_POST['id_produit_souhaite'] ?? null;
    $marque_propose = trim($_POST['marque_propose'] ?? '');
    $nom_produit_propose = trim($_POST['nom_produit_propose'] ?? '');
    $categorie_propose = $_POST['categorie_propose'] ?? '';
    $etat_estime = $_POST['etat_estime'] ?? '';
    $description_propose = trim($_POST['description_propose'] ?? '');
    $valeur_estimee = $_POST['valeur_estimee'] ?? null;
    $annee_achat = $_POST['annee_achat'] ?? null;
    $avec_boite = isset($_POST['avec_boite']) ? 1 : 0;
    $avec_certificat = isset($_POST['avec_certificat']) ? 1 : 0;
    $avec_accessoires = isset($_POST['avec_accessoires']) ? 1 : 0;
    
    if (empty($id_produit_souhaite)) $erreurs[] = "Le produit souhaité est requis.";
    if (empty($marque_propose)) $erreurs[] = "La marque de votre article est requise.";
    if (empty($nom_produit_propose)) $erreurs[] = "Le nom de votre article est requis.";
    if (empty($categorie_propose)) $erreurs[] = "La catégorie est requise.";
    if (empty($etat_estime)) $erreurs[] = "L'état estimé est requis.";
    if (empty($description_propose)) $erreurs[] = "La description est requise.";
    
    // Gestion des photos
    $photos = [];
    for ($i = 1; $i <= 4; $i++) {
        if (isset($_FILES["photo_$i"]) && $_FILES["photo_$i"]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES["photo_$i"];
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'echange_' . uniqid() . '.' . $extension;
            $destination = __DIR__ . '/public/images/echanges/' . $filename;
            
            if (!is_dir(__DIR__ . '/public/images/echanges/')) {
                mkdir(__DIR__ . '/public/images/echanges/', 0777, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $photos["photo_$i"] = $filename;
            }
        } else {
            $photos["photo_$i"] = null;
        }
    }
    
    if (empty($erreurs)) {
        if (!isset($_SESSION['client_id'])) {
            $_SESSION['echange_data'] = array_merge($_POST, $photos);
            header('Location: inscription.php?redirect=echanger&id=' . $id_produit_souhaite);
            exit();
        }
        
        $data = [
            'id_client' => $_SESSION['client_id'],
            'id_produit_souhaite' => $id_produit_souhaite,
            'marque_propose' => $marque_propose,
            'nom_produit_propose' => $nom_produit_propose,
            'categorie_propose' => $categorie_propose,
            'etat_estime' => $etat_estime,
            'description_propose' => $description_propose,
            'valeur_estimee' => $valeur_estimee,
            'annee_achat' => $annee_achat,
            'avec_boite' => $avec_boite,
            'avec_certificat' => $avec_certificat,
            'avec_accessoires' => $avec_accessoires,
            'photo_1' => $photos['photo_1'],
            'photo_2' => $photos['photo_2'],
            'photo_3' => $photos['photo_3'],
            'photo_4' => $photos['photo_4']
        ];
        
        if (DemandeEchange::creer($data)) {
            $success = true;
        } else {
            $erreurs[] = "Une erreur est survenue lors de l'envoi de votre demande.";
        }
    }
    if (DemandeEchange::creer($data)) {
    $success = true;
    
    // 📧 Envoyer email de confirmation au client
    require_once __DIR__ . '/includes/email_config.php';
    EmailService::notifierStatutEchange(
        $_SESSION['client_email'] ?? $email,
        $prenom . ' ' . $nom,
        $pdo->lastInsertId(),
        'en attente',
        null,
        null,
        'Votre proposition d\'échange a bien été reçue. Nos experts vont l\'examiner sous 48h.'
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
            <h1 class="text-5xl md:text-6xl font-serif italic mb-6">Échangez votre Pièce de Luxe</h1>
            <p class="text-gray-600 text-lg max-w-3xl mx-auto">
                Vous avez un article de luxe et vous souhaitez l'échanger contre une pièce de notre collection ? 
                DIAMON facilite l'échange entre passionnés avec une expertise professionnelle.
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
                    <h2 class="text-2xl font-serif italic mb-3">Demande d'Échange Envoyée !</h2>
                    <p class="mb-6">
                        Votre proposition d'échange a été transmise à nos experts. 
                        Vous recevrez une réponse sous 48h ouvrées.
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
            <h2 class="text-3xl font-serif italic text-center mb-12">Comment fonctionne l'échange ?</h2>
            <div class="max-w-5xl mx-auto grid md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gold text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-serif">1</div>
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-2">Choisissez le Produit</h3>
                    <p class="text-sm text-gray-600">Sélectionnez l'article de notre catalogue qui vous intéresse</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-gold text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-serif">2</div>
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-2">Proposez votre Article</h3>
                    <p class="text-sm text-gray-600">Décrivez et photographiez l'article que vous proposez</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-gold text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-serif">3</div>
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-2">Évaluation Expert</h3>
                    <p class="text-sm text-gray-600">Nos experts évaluent les deux articles et calculent la différence</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-gold text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-serif">4</div>
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-2">Finalisez l'Échange</h3>
                    <p class="text-sm text-gray-600">Validez et procédez à l'échange sécurisé</p>
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <?php if (!$success): ?>
        <div class="max-w-6xl mx-auto">
            
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
                
                <div class="grid lg:grid-cols-2 gap-8">
                    
                    <!-- Produit Souhaité -->
                    <!-- Produit Souhaité -->
<div class="bg-white border-2 border-gold p-8 rounded-lg">
    <h2 class="text-2xl font-serif italic mb-6">Article que vous Souhaitez</h2>
    
    <?php if ($produit_souhaite): ?>
        <input type="hidden" name="id_produit_souhaite" value="<?= $produit_souhaite['id'] ?>">
        <div class="bg-[#F9F8F6] p-6 rounded">
            <img src="<?= $base_url ?>/public/images/produits/<?= $produit_souhaite['image_principale'] ?>" 
                 alt="<?= htmlspecialchars($produit_souhaite['nom']) ?>"
                 class="w-full h-64 object-cover mb-4 rounded">
            <h3 class="text-xs uppercase tracking-widest font-bold mb-2">
                <?= htmlspecialchars($produit_souhaite['marque']) ?>
            </h3>
            <p class="text-lg font-serif mb-3">
                <?= htmlspecialchars($produit_souhaite['nom']) ?>
            </p>
            <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                <?= htmlspecialchars($produit_souhaite['description']) ?>
            </p>
            <div class="flex justify-between items-center">
                <span class="bg-black text-white px-3 py-1 text-[9px] font-bold uppercase tracking-widest">
                    Grade <?= htmlspecialchars($produit_souhaite['grade_code']) ?>
                </span>
                <span class="text-2xl font-serif text-gold">
                    <?= number_format($produit_souhaite['prix'], 0, ',', ' ') ?> €
                </span>
            </div>
        </div>
        <div class="mt-4 text-center">
            <a href="<?= $base_url ?>/catalogue.php" class="text-xs text-gray-500 hover:text-gold underline">
                Choisir un autre produit
            </a>
        </div>
    <?php else: ?>
        <div class="bg-yellow-50 border-2 border-yellow-200 p-8 rounded text-center">
            <svg class="w-16 h-16 text-yellow-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <p class="text-gray-700 font-semibold mb-4">Aucun produit sélectionné</p>
            <p class="text-gray-600 mb-6 text-sm">
                Veuillez d'abord choisir un article depuis notre catalogue pour pouvoir proposer un échange.
            </p>
            <a href="<?= $base_url ?>/catalogue.php" 
               class="inline-block bg-black text-white px-8 py-3 text-xs font-bold uppercase tracking-widest hover:bg-gold transition">
                Voir le Catalogue
            </a>
        </div>
    <?php endif; ?>
</div>

                    <!-- Article Proposé -->
                    <div class="bg-white border border-gray-200 p-8 rounded-lg">
                        <h2 class="text-2xl font-serif italic mb-6">Article que vous Proposez</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Marque *</label>
                                <input type="text" 
                                       name="marque_propose" 
                                       value="<?= htmlspecialchars($_POST['marque_propose'] ?? '') ?>"
                                       placeholder="Ex: Hermès, Rolex..."
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Nom du Produit *</label>
                                <input type="text" 
                                       name="nom_produit_propose" 
                                       value="<?= htmlspecialchars($_POST['nom_produit_propose'] ?? '') ?>"
                                       placeholder="Ex: Sac Kelly..."
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Catégorie *</label>
                                <select name="categorie_propose" 
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                                    <option value="">-- Choisir --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['slug'] ?>">
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
                                    <option value="A++">A++ - État Boutique</option>
                                    <option value="A+">A+ - État Exceptionnel</option>
                                    <option value="A">A - Excellent État</option>
                                    <option value="B">B - Très Bon État</option>
                                    <option value="C">C - Bon État</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Valeur Estimée (€)</label>
                                <input type="number" 
                                       name="valeur_estimee" 
                                       value="<?= htmlspecialchars($_POST['valeur_estimee'] ?? '') ?>"
                                       min="0"
                                       step="0.01"
                                       placeholder="8000"
                                       class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                                <p class="text-xs text-gray-500 mt-1">Estimation personnelle</p>
                            </div>

                            <div>
                                <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Année d'Achat</label>
                                <input type="number" 
                                       name="annee_achat" 
                                       value="<?= htmlspecialchars($_POST['annee_achat'] ?? '') ?>"
                                       min="1900" 
                                       max="<?= date('Y') ?>"
                                       class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white border border-gray-200 p-8 rounded-lg">
                    <h3 class="text-xl font-serif italic mb-6">Description de votre Article</h3>
                    
                    <div class="mb-6">
                        <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Description Détaillée *</label>
                        <textarea name="description_propose" 
                                  rows="6"
                                  required
                                  placeholder="Décrivez votre article : origine, état, histoire..."
                                  class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none"><?= htmlspecialchars($_POST['description_propose'] ?? '') ?></textarea>
                    </div>

                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="avec_boite" class="mr-3">
                            <span class="text-sm">J'ai la boîte d'origine</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="avec_certificat" class="mr-3">
                            <span class="text-sm">J'ai le certificat / Facture</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="avec_accessoires" class="mr-3">
                            <span class="text-sm">J'ai les accessoires d'origine</span>
                        </label>
                    </div>
                </div>

                <!-- Photos -->
                <div class="bg-white border border-gray-200 p-8 rounded-lg">
                    <h3 class="text-xl font-serif italic mb-6">Photos de votre Article</h3>
                    
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
                            <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Photo 4</label>
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
                            <?= !$produit_souhaite ? 'disabled' : '' ?>
                            class="bg-black text-white px-16 py-5 text-xs font-bold uppercase tracking-widest hover:bg-gold transition duration-300 disabled:bg-gray-400 disabled:cursor-not-allowed">
                        Proposer l'Échange
                    </button>
                    <p class="text-xs text-gray-500 mt-4">
                        Évaluation gratuite sous 48h
                    </p>
                </div>

            </form>
        </div>
        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>