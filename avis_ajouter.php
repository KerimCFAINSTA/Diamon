<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/models/Avis.php';
require_once __DIR__ . '/models/Produit.php';

$base_url = '/diamon_luxe';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['client_id'])) {
    header('Location: ' . $base_url . '/connexion.php');
    exit();
}

$id_client = $_SESSION['client_id'];
$id_produit = $_GET['produit'] ?? null;

if (!$id_produit) {
    header('Location: ' . $base_url . '/mon-compte.php');
    exit();
}

$produit = Produit::getById($id_produit);

if (!$produit) {
    header('Location: ' . $base_url . '/mon-compte.php');
    exit();
}

// Vérifier si le client peut laisser un avis
$commande = Avis::peutLaisserAvis($id_client, $id_produit);

if (!$commande) {
    $_SESSION['error'] = "Vous devez avoir acheté ce produit pour laisser un avis.";
    header('Location: ' . $base_url . '/views/produit_detail.php?id=' . $id_produit);
    exit();
}

$erreurs = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = intval($_POST['note'] ?? 0);
    $titre = trim($_POST['titre'] ?? '');
    $commentaire = trim($_POST['commentaire'] ?? '');
    $recommande = isset($_POST['recommande']) ? 1 : 0;
    
    // Validation
    if ($note < 1 || $note > 5) {
        $erreurs[] = "Veuillez sélectionner une note entre 1 et 5 étoiles.";
    }
    
    if (empty($titre)) {
        $erreurs[] = "Le titre est requis.";
    } elseif (strlen($titre) < 5) {
        $erreurs[] = "Le titre doit contenir au moins 5 caractères.";
    }
    
    if (empty($commentaire)) {
        $erreurs[] = "Le commentaire est requis.";
    } elseif (strlen($commentaire) < 20) {
        $erreurs[] = "Le commentaire doit contenir au moins 20 caractères.";
    }
    
    // Traiter les photos
    $photos_uploaded = [];
    if (!empty($_FILES['photos']['name'][0])) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                $file_type = $_FILES['photos']['type'][$key];
                $file_size = $_FILES['photos']['size'][$key];
                
                if (!in_array($file_type, $allowed_types)) {
                    $erreurs[] = "Format d'image non autorisé. Utilisez JPG, PNG ou WEBP.";
                    continue;
                }
                
                if ($file_size > $max_size) {
                    $erreurs[] = "L'image est trop volumineuse (max 5MB).";
                    continue;
                }
                
                // Générer un nom unique
                $extension = pathinfo($_FILES['photos']['name'][$key], PATHINFO_EXTENSION);
                $filename = uniqid('avis_') . '.' . $extension;
                $filepath = __DIR__ . '/public/images/avis/' . $filename;
                
                if (move_uploaded_file($tmp_name, $filepath)) {
                    $photos_uploaded[] = $filename;
                }
            }
        }
    }
    
    // Si pas d'erreurs, créer l'avis
    if (empty($erreurs)) {
        $data = [
            'id_produit' => $id_produit,
            'id_client' => $id_client,
            'id_commande' => $commande['id_commande'],
            'note' => $note,
            'titre' => $titre,
            'commentaire' => $commentaire,
            'recommande' => $recommande,
            'date_achat' => date('Y-m-d')
        ];
        
        $id_avis = Avis::creer($data);
        
        if ($id_avis) {
            // Ajouter les photos
            if (!empty($photos_uploaded)) {
                Avis::ajouterPhotos($id_avis, $photos_uploaded);
            }
            
            $_SESSION['success'] = "Merci pour votre avis ! Il sera visible après modération.";
            header('Location: ' . $base_url . '/views/produit_detail.php?id=' . $id_produit);
            exit();
        } else {
            $erreurs[] = "Erreur lors de l'enregistrement de votre avis.";
        }
    }
}

$page_title = "Laisser un avis | DIAMON";
require_once __DIR__ . '/includes/header.php';
?>

<main class="pt-32 pb-24">
    <div class="max-w-4xl mx-auto px-6">
        
        <div class="mb-12">
            <h1 class="text-4xl font-serif italic mb-2">Laisser un Avis</h1>
            <p class="text-gray-600">Partagez votre expérience avec ce produit</p>
        </div>

        <?php if (!empty($erreurs)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded mb-8">
                <strong>Erreurs :</strong>
                <ul class="list-disc list-inside mt-2">
                    <?php foreach ($erreurs as $erreur): ?>
                        <li><?= htmlspecialchars($erreur) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Produit -->
        <div class="bg-gray-50 p-6 rounded-lg mb-8 flex items-center space-x-6">
            <div class="w-24 h-24 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                <?php if ($produit['image_principale']): ?>
                    <img src="<?= $base_url ?>/public/images/produits/<?= $produit['image_principale'] ?>" 
                         class="w-full h-full object-cover"
                         alt="<?= htmlspecialchars($produit['nom']) ?>">
                <?php endif; ?>
            </div>
            <div>
                <p class="text-xs uppercase tracking-widest font-bold text-gray-500">
                    <?= htmlspecialchars($produit['marque']) ?>
                </p>
                <h3 class="text-xl font-serif italic">
                    <?= htmlspecialchars($produit['nom']) ?>
                </h3>
                <p class="text-gold font-semibold mt-1">
                    <?= number_format($produit['prix'], 0, ',', ' ') ?> €
                </p>
            </div>
        </div>

        <!-- Formulaire -->
        <form method="post" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-8 space-y-8">
            
            <!-- Note -->
            <div>
                <label class="block text-sm font-semibold mb-3">Votre Note *</label>
                <div class="flex items-center space-x-2" id="rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="note" value="<?= $i ?>" class="hidden rating-input" required>
                            <svg class="w-10 h-10 star text-gray-300 hover:text-gold transition" 
                                 fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </label>
                    <?php endfor; ?>
                </div>
                <p class="text-xs text-gray-500 mt-2">Cliquez pour noter de 1 à 5 étoiles</p>
            </div>

            <!-- Titre -->
            <div>
                <label class="block text-sm font-semibold mb-2">Titre de votre avis *</label>
                <input type="text" 
                       name="titre" 
                       value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>"
                       required
                       maxlength="200"
                       placeholder="Résumez votre expérience en quelques mots"
                       class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
            </div>

            <!-- Commentaire -->
            <div>
                <label class="block text-sm font-semibold mb-2">Votre Commentaire *</label>
                <textarea name="commentaire" 
                          rows="6"
                          required
                          minlength="20"
                          placeholder="Décrivez votre expérience avec ce produit (minimum 20 caractères)..."
                          class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none"><?= htmlspecialchars($_POST['commentaire'] ?? '') ?></textarea>
                <p class="text-xs text-gray-500 mt-1">Minimum 20 caractères</p>
            </div>

            <!-- Photos -->
            <div>
                <label class="block text-sm font-semibold mb-2">Photos (optionnel)</label>
                <input type="file" 
                       name="photos[]" 
                       accept="image/jpeg,image/jpg,image/png,image/webp"
                       multiple
                       max="4"
                       class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                <p class="text-xs text-gray-500 mt-1">Maximum 4 photos - JPG, PNG ou WEBP (max 5MB chacune)</p>
            </div>

            <!-- Recommandation -->
            <div>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" 
                           name="recommande" 
                           <?= isset($_POST['recommande']) || !isset($_POST['note']) ? 'checked' : '' ?>
                           class="w-5 h-5 text-gold border-gray-300 rounded focus:ring-gold">
                    <span class="ml-3 text-sm font-semibold">Je recommande ce produit</span>
                </label>
            </div>

            <!-- Note importante -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
                <p class="text-sm text-blue-800">
                    <strong>ℹ️ À savoir :</strong> Votre avis sera vérifié par notre équipe avant publication. 
                    Seuls les avis conformes à nos <a href="#" class="underline">conditions d'utilisation</a> seront publiés.
                </p>
            </div>

            <!-- Boutons -->
            <div class="flex justify-between items-center pt-6 border-t">
                <a href="<?= $base_url ?>/views/produit_detail.php?id=<?= $id_produit ?>" 
                   class="px-6 py-3 border border-gray-300 rounded text-sm font-semibold hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-8 py-3 bg-black text-white rounded text-sm font-bold uppercase tracking-widest hover:bg-gold transition">
                    Publier mon Avis
                </button>
            </div>

        </form>

    </div>
</main>

<script>
    // Gestion des étoiles
    const stars = document.querySelectorAll('.star');
    const ratingInputs = document.querySelectorAll('.rating-input');
    
    ratingInputs.forEach((input, index) => {
        input.addEventListener('change', function() {
            updateStars(index + 1);
        });
    });
    
    stars.forEach((star, index) => {
        star.addEventListener('click', function() {
            ratingInputs[index].checked = true;
            updateStars(index + 1);
        });
        
        star.addEventListener('mouseenter', function() {
            highlightStars(index + 1);
        });
    });
    
    document.getElementById('rating').addEventListener('mouseleave', function() {
        const checkedIndex = Array.from(ratingInputs).findIndex(input => input.checked);
        updateStars(checkedIndex + 1);
    });
    
    function highlightStars(count) {
        stars.forEach((star, index) => {
            if (index < count) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-gold');
            } else {
                star.classList.add('text-gray-300');
                star.classList.remove('text-gold');
            }
        });
    }
    
    function updateStars(count) {
        highlightStars(count);
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>