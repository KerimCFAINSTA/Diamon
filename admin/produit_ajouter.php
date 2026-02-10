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
    
    // Gestion de l'upload d'image
    $image_principale = null;
    if (isset($_FILES['image_principale']) && $_FILES['image_principale']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5 MB
        
        if (!in_array($_FILES['image_principale']['type'], $allowed_types)) {
            $erreurs[] = "Format d'image non autorisé. Utilisez JPG, PNG ou WEBP.";
        } elseif ($_FILES['image_principale']['size'] > $max_size) {
            $erreurs[] = "L'image est trop volumineuse (max 5 MB).";
        } else {
            $extension = pathinfo($_FILES['image_principale']['name'], PATHINFO_EXTENSION);
            $image_principale = uniqid('prod_') . '.' . $extension;
            $upload_path = __DIR__ . '/../public/images/produits/' . $image_principale;
            
            if (!move_uploaded_file($_FILES['image_principale']['tmp_name'], $upload_path)) {
                $erreurs[] = "Erreur lors de l'upload de l'image.";
                $image_principale = null;
            }
        }
    }
    
    // Si pas d'erreurs, créer le produit
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
        
        if (Produit::creer($data)) {
            $success = true;
            
            // Récupérer l'ID du produit créé
            $produit_id = $pdo->lastInsertId();
            
            // Gérer les images supplémentaires
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
                    Produit::ajouterImages($produit_id, $images_supp);
                }
            }
            
            header('Location: produits.php?success=1');
            exit();
        } else {
            $erreurs[] = "Erreur lors de la création du produit.";
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
    <title>Ajouter un Produit | DIAMON Admin</title>
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

    <div class="max-w-4xl mx-auto px-6 py-12">
        
        <h1 class="text-3xl font-bold mb-8">Ajouter un Produit</h1>

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

        <form method="post" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-8 space-y-6">
            
            <div class="grid md:grid-cols-2 gap-6">
                
                <!-- Marque -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Marque *</label>
                    <input type="text" 
                           name="marque" 
                           value="<?= htmlspecialchars($_POST['marque'] ?? '') ?>"
                           required
                           placeholder="Ex: Hermès"
                           class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                </div>

                <!-- Nom du produit -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Nom du Produit *</label>
                    <input type="text" 
                           name="nom" 
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
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
                          class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                
                <!-- Prix -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Prix (€) *</label>
                    <input type="number" 
                           name="prix" 
                           value="<?= htmlspecialchars($_POST['prix'] ?? '') ?>"
                           step="0.01"
                           min="0"
                           required
                           placeholder="8500"
                           class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                </div>

                <!-- Stock -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Stock *</label>
                    <input type="number" 
                           name="stock" 
                           value="<?= htmlspecialchars($_POST['stock'] ?? '1') ?>"
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
                               <?= isset($_POST['disponible']) || !isset($_POST['marque']) ? 'checked' : '' ?>
                               class="w-5 h-5 text-gold border-gray-300 rounded focus:ring-gold">
                        <span class="ml-3 text-sm text-gray-700">Produit disponible à la vente</span>
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
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($_POST['id_categorie']) && $_POST['id_categorie'] == $cat['id']) ? 'selected' : '' ?>>
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
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($grades as $grade): ?>
                            <option value="<?= $grade['id'] ?>" <?= (isset($_POST['id_grade']) && $_POST['id_grade'] == $grade['id']) ? 'selected' : '' ?>>
                                Grade <?= htmlspecialchars($grade['code']) ?> - <?= htmlspecialchars($grade['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <!-- Image principale -->
            <div>
                <label class="block text-sm font-semibold mb-2">Image Principale</label>
                <input type="file" 
                       name="image_principale" 
                       accept="image/jpeg,image/jpg,image/png,image/webp"
                       class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                <p class="text-xs text-gray-500 mt-1">Formats acceptés : JPG, PNG, WEBP (max 5 MB)</p>
            </div>

            <!-- Images supplémentaires -->
            <div>
                <label class="block text-sm font-semibold mb-2">Images Supplémentaires (max 4)</label>
                <input type="file" 
                       name="images_supplementaires[]" 
                       accept="image/jpeg,image/jpg,image/png,image/webp"
                       multiple
                       class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                <p class="text-xs text-gray-500 mt-1">Vous pouvez sélectionner plusieurs images</p>
            </div>

            <!-- Boutons -->
            <div class="flex justify-between items-center pt-6 border-t">
                <a href="produits.php" 
                   class="px-6 py-3 border border-gray-300 rounded text-sm font-semibold hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-8 py-3 bg-black text-white rounded text-sm font-bold uppercase tracking-widest hover:bg-gold transition">
                    Ajouter le Produit
                </button>
            </div>

        </form>

    </div>

</body>
</html>