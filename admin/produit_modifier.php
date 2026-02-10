<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/Produit.php';
require_once __DIR__ . '/../models/Categorie.php';
require_once __DIR__ . '/../models/Grade.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: produits.php');
    exit();
}

// Récupérer le produit
$produit = Produit::getById($id);

if (!$produit) {
    header('Location: produits.php?error=not_found');
    exit();
}

// Récupérer les images du produit
$images = Produit::getImages($id);

$categories = Categorie::getAll();
$grades = Grade::getAll();

$success = false;
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marque = trim($_POST['marque'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = floatval($_POST['prix'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $id_categorie = intval($_POST['id_categorie'] ?? 0);
    $id_grade = intval($_POST['id_grade'] ?? 0);
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    
    // Validation
    if (empty($marque)) $erreurs[] = "La marque est requise.";
    if (empty($nom)) $erreurs[] = "Le nom du produit est requis.";
    if ($prix <= 0) $erreurs[] = "Le prix doit être supérieur à 0.";
    if ($stock < 0) $erreurs[] = "Le stock ne peut pas être négatif.";
    if ($id_categorie <= 0) $erreurs[] = "Veuillez sélectionner une catégorie.";
    if ($id_grade <= 0) $erreurs[] = "Veuillez sélectionner un grade.";
    
    // Gestion de la nouvelle image principale
    $image_principale = $produit['image_principale'];
    if (isset($_FILES['image_principale']) && $_FILES['image_principale']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5 MB
        
        if (!in_array($_FILES['image_principale']['type'], $allowed_types)) {
            $erreurs[] = "Format d'image non autorisé. Utilisez JPG, PNG ou WEBP.";
        } elseif ($_FILES['image_principale']['size'] > $max_size) {
            $erreurs[] = "L'image est trop volumineuse (max 5 MB).";
        } else {
            // Supprimer l'ancienne image
            if ($produit['image_principale']) {
                $old_image = __DIR__ . '/../public/images/produits/' . $produit['image_principale'];
                if (file_exists($old_image)) {
                    unlink($old_image);
                }
            }
            
            $extension = pathinfo($_FILES['image_principale']['name'], PATHINFO_EXTENSION);
            $image_principale = uniqid('prod_') . '.' . $extension;
            $upload_path = __DIR__ . '/../public/images/produits/' . $image_principale;
            
            if (!move_uploaded_file($_FILES['image_principale']['tmp_name'], $upload_path)) {
                $erreurs[] = "Erreur lors de l'upload de l'image.";
                $image_principale = $produit['image_principale'];
            }
        }
    }
    
    // Si pas d'erreurs, mettre à jour le produit
    if (empty($erreurs)) {
        $data = [
            'marque' => $marque,
            'nom' => $nom,
            'description' => $description,
            'prix' => $prix,
            'stock' => $stock,
            'id_categorie' => $id_categorie,
            'id_grade' => $id_grade,
            'disponible' => $disponible,
            'image_principale' => $image_principale
        ];
        
        if (Produit::update($id, $data)) {
            $success = true;
            
            // Gérer les nouvelles images supplémentaires
            if (isset($_FILES['images_supplementaires'])) {
                $images_supp = [];
                foreach ($_FILES['images_supplementaires']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images_supplementaires']['error'][$key] === 0) {
                        $extension = pathinfo($_FILES['images_supplementaires']['name'][$key], PATHINFO_EXTENSION);
                        $filename = uniqid('prod_') . '.' . $extension;
                        $upload_path = __DIR__ . '/../public/images/produits/' . $filename;
                        
                        if (move_uploaded_file($tmp_name, $upload_path)) {
                            $images_supp[] = $filename;
                        }
                    }
                }
                
                if (!empty($images_supp)) {
                    Produit::ajouterImages($id, $images_supp);
                }
            }
            
            // Recharger le produit
            $produit = Produit::getById($id);
            $images = Produit::getImages($id);
        } else {
            $erreurs[] = "Erreur lors de la mise à jour du produit.";
        }
    }
}

$base_url = '/diamon_luxe';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Produit #<?= $produit['id'] ?> | DIAMON Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .bg-gold { background-color: #C5A059; }
        .text-gold { color: #C5A059; }
    </style>
</head>
<body class="bg-gray-100">
    
    <nav class="bg-black text-white p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-serif">DIAMON - Admin</h1>
            <a href="produits.php" class="text-sm hover:text-gold">← Retour aux produits</a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-6 py-12">
        
        <h1 class="text-3xl font-bold mb-2">Modifier le Produit #<?= $produit['id'] ?></h1>
        <p class="text-gray-600 mb-8"><?= htmlspecialchars($produit['marque']) ?> - <?= htmlspecialchars($produit['nom']) ?></p>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-800 px-6 py-4 rounded mb-8">
                ✓ Produit mis à jour avec succès !
            </div>
        <?php endif; ?>

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

        <div class="grid lg:grid-cols-3 gap-8">
            
            <!-- Formulaire principal -->
            <div class="lg:col-span-2">
                <form method="post" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-8 space-y-6">
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        
                        <!-- Marque -->
                        <div>
                            <label class="block text-sm font-semibold mb-2">Marque *</label>
                            <input type="text" 
                                   name="marque" 
                                   value="<?= htmlspecialchars($produit['marque']) ?>"
                                   required
                                   placeholder="Ex: Hermès"
                                   class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        </div>

                        <!-- Nom du produit -->
                        <div>
                            <label class="block text-sm font-semibold mb-2">Nom du Produit *</label>
                            <input type="text" 
                                   name="nom" 
                                   value="<?= htmlspecialchars($produit['nom']) ?>"
                                   required
                                   placeholder="Ex: Sac Birkin 35"
                                   class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        </div>

                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Description</label>
                        <textarea name="description" 
                                  rows="5"
                                  placeholder="Description détaillée du produit..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none"><?= htmlspecialchars($produit['description']) ?></textarea>
                    </div>

                    <div class="grid md:grid-cols-3 gap-6">
                        
                        <!-- Prix -->
                        <div>
                            <label class="block text-sm font-semibold mb-2">Prix (€) *</label>
                            <input type="number" 
                                   name="prix" 
                                   value="<?= htmlspecialchars($produit['prix']) ?>"
                                   step="0.01"
                                   min="0"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        </div>

                        <!-- Stock -->
                        <div>
                            <label class="block text-sm font-semibold mb-2">Stock *</label>
                            <input type="number" 
                                   name="stock" 
                                   value="<?= htmlspecialchars($produit['stock']) ?>"
                                   min="0"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        </div>

                        <!-- Disponible -->
                        <div>
                            <label class="block text-sm font-semibold mb-2">Disponibilité</label>
                            <label class="flex items-center mt-3">
                                <input type="checkbox" 
                                       name="disponible" 
                                       <?= $produit['disponible'] ? 'checked' : '' ?>
                                       class="w-5 h-5 text-gold border-gray-300 rounded focus:ring-gold">
                                <span class="ml-3 text-sm text-gray-700">Disponible</span>
                            </label>
                        </div>

                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        
                        <!-- Catégorie -->
                        <div>
                            <label class="block text-sm font-semibold mb-2">Catégorie *</label>
                            <select name="id_categorie" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $produit['id_categorie'] == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Grade -->
                        <div>
                            <label class="block text-sm font-semibold mb-2">Grade *</label>
                            <select name="id_grade" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                                <?php foreach ($grades as $grade): ?>
                                    <option value="<?= $grade['id'] ?>" <?= $produit['id_grade'] == $grade['id'] ? 'selected' : '' ?>>
                                        Grade <?= htmlspecialchars($grade['code']) ?> - <?= htmlspecialchars($grade['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>

                    <!-- Image principale -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Remplacer l'Image Principale</label>
                        <?php if ($produit['image_principale']): ?>
                            <div class="mb-3">
                                <img src="<?= $base_url ?>/public/images/produits/<?= $produit['image_principale'] ?>" 
                                     alt="Image actuelle"
                                     class="w-32 h-32 object-cover rounded border-2 border-gray-200">
                                <p class="text-xs text-gray-500 mt-1">Image actuelle</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" 
                               name="image_principale" 
                               accept="image/jpeg,image/jpg,image/png,image/webp"
                               class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        <p class="text-xs text-gray-500 mt-1">Laissez vide pour conserver l'image actuelle</p>
                    </div>

                    <!-- Images supplémentaires -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Ajouter des Images Supplémentaires</label>
                        <input type="file" 
                               name="images_supplementaires[]" 
                               accept="image/jpeg,image/jpg,image/png,image/webp"
                               multiple
                               class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        <p class="text-xs text-gray-500 mt-1">Ces images s'ajouteront aux images existantes</p>
                    </div>

                    <!-- Boutons -->
                    <div class="flex justify-between items-center pt-6 border-t">
                        <a href="produits.php" 
                           class="px-6 py-3 border border-gray-300 rounded text-sm font-semibold hover:bg-gray-50">
                            Annuler
                        </a>
                        <button type="submit" 
                                class="px-8 py-3 bg-black text-white rounded text-sm font-bold uppercase tracking-widest hover:bg-gold transition">
                            Enregistrer les Modifications
                        </button>
                    </div>

                </form>
            </div>

            <!-- Sidebar - Images et infos -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Images existantes -->
                <?php if (!empty($images)): ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold mb-4">Galerie d'Images</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <?php foreach ($images as $img): ?>
                                <div class="relative group">
                                    <img src="<?= $base_url ?>/public/images/produits/<?= $img['chemin'] ?>" 
                                         alt="Image produit"
                                         class="w-full h-24 object-cover rounded">
                                    <a href="produit_supprimer_image.php?id=<?= $img['id'] ?>&produit=<?= $produit['id'] ?>" 
                                       onclick="return confirm('Supprimer cette image ?')"
                                       class="absolute top-1 right-1 bg-red-500 text-white p-1 rounded opacity-0 group-hover:opacity-100 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Informations -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold mb-4">Informations</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="text-gray-500">Créé le :</span>
                            <span class="font-semibold ml-2"><?= date('d/m/Y', strtotime($produit['created_at'])) ?></span>
                        </div>
                        <div>
                            <span class="text-gray-500">ID Produit :</span>
                            <span class="font-semibold ml-2">#<?= $produit['id'] ?></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Vues :</span>
                            <span class="font-semibold ml-2">-</span>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold mb-4">Actions Rapides</h3>
                    <div class="space-y-2">
                        <a href="<?= $base_url ?>/views/produit_detail.php?id=<?= $produit['id'] ?>" 
                           target="_blank"
                           class="block w-full px-4 py-2 bg-blue-100 text-blue-700 text-center rounded text-sm font-semibold hover:bg-blue-200">
                            👁️ Voir sur le Site
                        </a>
                        <a href="produit_supprimer.php?id=<?= $produit['id'] ?>" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')"
                           class="block w-full px-4 py-2 bg-red-100 text-red-700 text-center rounded text-sm font-semibold hover:bg-red-200">
                            🗑️ Supprimer le Produit
                        </a>
                    </div>
                </div>

            </div>

        </div>

    </div>

</body>
</html>