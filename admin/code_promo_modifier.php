<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/CodePromo.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: codes_promo.php');
    exit();
}

$code_promo = CodePromo::getById($id);

if (!$code_promo) {
    header('Location: codes_promo.php?error=not_found');
    exit();
}

$stats = CodePromo::getStatistiques($id);

$success = false;
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $type = $_POST['type'] ?? '';
    $valeur = floatval($_POST['valeur'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $montant_minimum = floatval($_POST['montant_minimum'] ?? 0);
    $date_debut = !empty($_POST['date_debut']) ? $_POST['date_debut'] : null;
    $date_fin = !empty($_POST['date_fin']) ? $_POST['date_fin'] : null;
    $limite_utilisation = !empty($_POST['limite_utilisation']) ? intval($_POST['limite_utilisation']) : null;
    $actif = isset($_POST['actif']) ? 1 : 0;
    
    // Validation (même que création)
    if (empty($code)) {
        $erreurs[] = "Le code est requis.";
    } elseif (strlen($code) < 3) {
        $erreurs[] = "Le code doit contenir au moins 3 caractères.";
    } elseif (!preg_match('/^[A-Z0-9-]+$/', $code)) {
        $erreurs[] = "Le code ne peut contenir que des lettres, chiffres et tirets.";
    }
    
    if (!in_array($type, ['pourcentage', 'montant_fixe'])) {
        $erreurs[] = "Type de réduction invalide.";
    }
    
    if ($valeur <= 0) {
        $erreurs[] = "La valeur de réduction doit être supérieure à 0.";
    }
    
    if ($type === 'pourcentage' && $valeur > 100) {
        $erreurs[] = "Le pourcentage ne peut pas dépasser 100%.";
    }
    
    if ($date_debut && $date_fin && $date_debut > $date_fin) {
        $erreurs[] = "La date de début doit être antérieure à la date de fin.";
    }
    
    // Vérifier si le code existe déjà (sauf si c'est le même)
    if (empty($erreurs) && $code !== $code_promo['code']) {
        $existing = CodePromo::getByCode($code);
        if ($existing && $existing['id'] != $id) {
            $erreurs[] = "Ce code promo existe déjà.";
        }
    }
    
    // Si pas d'erreurs, mettre à jour
    if (empty($erreurs)) {
        $data = [
            'code' => $code,
            'type' => $type,
            'valeur' => $valeur,
            'description' => $description,
            'montant_minimum' => $montant_minimum,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'limite_utilisation' => $limite_utilisation,
            'actif' => $actif
        ];
        
        if (CodePromo::update($id, $data)) {
            $success = true;
            $code_promo = CodePromo::getById($id);
        } else {
            $erreurs[] = "Erreur lors de la mise à jour du code promo.";
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
    <title>Modifier Code Promo #<?= $code_promo['id'] ?> | DIAMON Admin</title>
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
            <a href="codes_promo.php" class="text-sm hover:text-gold">← Retour aux codes promo</a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-6 py-12">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Modifier le Code Promo</h1>
            <p class="text-gray-600 font-mono text-xl"><?= htmlspecialchars($code_promo['code']) ?></p>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-800 px-6 py-4 rounded mb-8">
                ✓ Code promo mis à jour avec succès !
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
                <form method="post" class="bg-white rounded-lg shadow p-8 space-y-6">
                    
                    <!-- Code -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Code Promo *</label>
                        <input type="text" 
                               name="code" 
                               value="<?= htmlspecialchars($code_promo['code']) ?>"
                               required
                               maxlength="50"
                               class="w-full px-4 py-3 border border-gray-300 rounded uppercase focus:border-gold focus:outline-none font-mono text-lg">
                    </div>

                    <!-- Type et Valeur -->
                    <div class="grid md:grid-cols-2 gap-6">
                        
                        <div>
                            <label class="block text-sm font-semibold mb-2">Type de Réduction *</label>
                            <select name="type" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                                <option value="pourcentage" <?= $code_promo['type'] === 'pourcentage' ? 'selected' : '' ?>>
                                    Pourcentage (%)
                                </option>
                                <option value="montant_fixe" <?= $code_promo['type'] === 'montant_fixe' ? 'selected' : '' ?>>
                                    Montant Fixe (€)
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2">Valeur *</label>
                            <input type="number" 
                                   name="valeur" 
                                   value="<?= htmlspecialchars($code_promo['valeur']) ?>"
                                   step="0.01"
                                   min="0"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        </div>

                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Description</label>
                        <textarea name="description" 
                                  rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none"><?= htmlspecialchars($code_promo['description']) ?></textarea>
                    </div>

                    <!-- Conditions -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
                        <h3 class="font-bold mb-4">Conditions d'Utilisation</h3>
                        
                        <div class="space-y-4">
                            
                            <div>
                                <label class="block text-sm font-semibold mb-2">Montant Minimum (€)</label>
                                <input type="number" 
                                       name="montant_minimum" 
                                       value="<?= htmlspecialchars($code_promo['montant_minimum']) ?>"
                                       step="0.01"
                                       min="0"
                                       class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold mb-2">Limite d'Utilisation</label>
                                <input type="number" 
                                       name="limite_utilisation" 
                                       value="<?= htmlspecialchars($code_promo['limite_utilisation'] ?? '') ?>"
                                       min="1"
                                       placeholder="Illimité"
                                       class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                            </div>

                        </div>
                    </div>

                    <!-- Période de validité -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded">
                        <h3 class="font-bold mb-4">Période de Validité</h3>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            
                            <div>
                                <label class="block text-sm font-semibold mb-2">Date de Début</label>
                                <input type="date" 
                                       name="date_debut" 
                                       value="<?= htmlspecialchars($code_promo['date_debut'] ?? '') ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold mb-2">Date de Fin</label>
                                <input type="date" 
                                       name="date_fin" 
                                       value="<?= htmlspecialchars($code_promo['date_fin'] ?? '') ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                            </div>

                        </div>
                    </div>

                    <!-- Statut -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="actif" 
                                   <?= $code_promo['actif'] ? 'checked' : '' ?>
                                   class="w-5 h-5 text-gold border-gray-300 rounded focus:ring-gold">
                            <span class="ml-3 text-sm font-semibold">Code promo actif</span>
                        </label>
                    </div>

                    <!-- Boutons -->
                    <div class="flex justify-between items-center pt-6 border-t">
                        <a href="codes_promo.php" 
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

            <!-- Sidebar - Statistiques -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Statistiques -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold mb-4">📊 Statistiques</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-600">Utilisations</p>
                            <p class="text-2xl font-bold"><?= $code_promo['nb_utilisations'] ?></p>
                        </div>
                        <?php if ($stats && $stats['nb_utilisations'] > 0): ?>
                            <div>
                                <p class="text-sm text-gray-600">Réductions Totales</p>
                                <p class="text-2xl font-bold text-gold">
                                    <?= number_format($stats['total_reductions'], 0, ',', ' ') ?> €
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Réduction Moyenne</p>
                                <p class="text-xl font-bold text-blue-600">
                                    <?= number_format($stats['moyenne_reduction'], 2, ',', ' ') ?> €
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold mb-4">Actions Rapides</h3>
                    <div class="space-y-2">
                        <a href="code_promo_supprimer.php?id=<?= $code_promo['id'] ?>" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce code promo ?')"
                           class="block w-full px-4 py-2 bg-red-100 text-red-700 text-center rounded text-sm font-semibold hover:bg-red-200">
                            🗑️ Supprimer le Code
                        </a>
                    </div>
                </div>

                <!-- Infos -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold mb-4">Informations</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="text-gray-500">Créé le :</span>
                            <span class="font-semibold ml-2">
                                <?= date('d/m/Y', strtotime($code_promo['created_at'])) ?>
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500">Dernière MAJ :</span>
                            <span class="font-semibold ml-2">
                                <?= date('d/m/Y', strtotime($code_promo['updated_at'])) ?>
                            </span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

</body>
</html>