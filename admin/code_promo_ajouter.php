<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/CodePromo.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

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
    
    // Validation
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
    
    // Vérifier si le code existe déjà
    if (empty($erreurs) && CodePromo::getByCode($code)) {
        $erreurs[] = "Ce code promo existe déjà.";
    }
    
    // Si pas d'erreurs, créer le code promo
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
        
        if (CodePromo::creer($data)) {
            header('Location: codes_promo.php?success=1');
            exit();
        } else {
            $erreurs[] = "Erreur lors de la création du code promo.";
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
    <title>Créer un Code Promo | DIAMON Admin</title>
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

    <div class="max-w-4xl mx-auto px-6 py-12">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Créer un Code Promo</h1>
            <p class="text-gray-600">Configurez une nouvelle promotion pour vos clients</p>
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

        <form method="post" class="bg-white rounded-lg shadow p-8 space-y-6">
            
            <!-- Code -->
            <div>
                <label class="block text-sm font-semibold mb-2">Code Promo *</label>
                <input type="text" 
                       name="code" 
                       value="<?= htmlspecialchars($_POST['code'] ?? '') ?>"
                       required
                       maxlength="50"
                       placeholder="Ex: NOEL2024"
                       class="w-full px-4 py-3 border border-gray-300 rounded uppercase focus:border-gold focus:outline-none font-mono text-lg">
                <p class="text-xs text-gray-500 mt-1">Lettres majuscules, chiffres et tirets uniquement</p>
            </div>

            <!-- Type et Valeur -->
            <div class="grid md:grid-cols-2 gap-6">
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Type de Réduction *</label>
                    <select name="type" 
                            required
                            onchange="updateReductionPlaceholder(this)"
                            class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        <option value="pourcentage" <?= (isset($_POST['type']) && $_POST['type'] === 'pourcentage') ? 'selected' : '' ?>>
                            Pourcentage (%)
                        </option>
                        <option value="montant_fixe" <?= (isset($_POST['type']) && $_POST['type'] === 'montant_fixe') ? 'selected' : '' ?>>
                            Montant Fixe (€)
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Valeur *</label>
                    <input type="number" 
                           name="valeur" 
                           value="<?= htmlspecialchars($_POST['valeur'] ?? '') ?>"
                           step="0.01"
                           min="0"
                           required
                           id="valeurInput"
                           placeholder="Ex: 10 ou 50"
                           class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                </div>

            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-semibold mb-2">Description</label>
                <textarea name="description" 
                          rows="3"
                          placeholder="Description interne du code promo..."
                          class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <!-- Conditions -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
                <h3 class="font-bold mb-4 flex items-center">
                    <span class="mr-2">⚙️</span> Conditions d'Utilisation
                </h3>
                
                <div class="space-y-4">
                    
                    <!-- Montant minimum -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Montant Minimum de Commande (€)</label>
                        <input type="number" 
                               name="montant_minimum" 
                               value="<?= htmlspecialchars($_POST['montant_minimum'] ?? '0') ?>"
                               step="0.01"
                               min="0"
                               placeholder="0 = aucun minimum"
                               class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        <p class="text-xs text-gray-600 mt-1">Laissez à 0 pour aucune condition de montant</p>
                    </div>

                    <!-- Limite d'utilisation -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Limite d'Utilisation Globale</label>
                        <input type="number" 
                               name="limite_utilisation" 
                               value="<?= htmlspecialchars($_POST['limite_utilisation'] ?? '') ?>"
                               min="1"
                               placeholder="Illimité"
                               class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        <p class="text-xs text-gray-600 mt-1">Nombre maximum d'utilisations total (vide = illimité)</p>
                    </div>

                </div>
            </div>

            <!-- Période de validité -->
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded">
                <h3 class="font-bold mb-4 flex items-center">
                    <span class="mr-2">📅</span> Période de Validité
                </h3>
                
                <div class="grid md:grid-cols-2 gap-6">
                    
                    <div>
                        <label class="block text-sm font-semibold mb-2">Date de Début</label>
                        <input type="date" 
                               name="date_debut" 
                               value="<?= htmlspecialchars($_POST['date_debut'] ?? '') ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        <p class="text-xs text-gray-600 mt-1">Laisser vide = actif immédiatement</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Date de Fin</label>
                        <input type="date" 
                               name="date_fin" 
                               value="<?= htmlspecialchars($_POST['date_fin'] ?? '') ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        <p class="text-xs text-gray-600 mt-1">Laisser vide = pas d'expiration</p>
                    </div>

                </div>
            </div>

            <!-- Statut -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="actif" 
                           <?= !isset($_POST['code']) || isset($_POST['actif']) ? 'checked' : '' ?>
                           class="w-5 h-5 text-gold border-gray-300 rounded focus:ring-gold">
                    <span class="ml-3 text-sm font-semibold">Code promo actif</span>
                </label>
                <p class="text-xs text-gray-500 mt-1 ml-8">Si décoché, le code ne pourra pas être utilisé</p>
            </div>

            <!-- Aperçu -->
            <div class="bg-gradient-to-r from-purple-500 to-pink-500 text-white p-6 rounded-lg">
                <h3 class="font-bold mb-3 flex items-center">
                    <span class="mr-2">👁️</span> Aperçu du Code Promo
                </h3>
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-80">Code :</p>
                            <p class="text-2xl font-bold font-mono" id="previewCode">-</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm opacity-80">Réduction :</p>
                            <p class="text-3xl font-bold" id="previewReduction">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutons -->
            <div class="flex justify-between items-center pt-6 border-t">
                <a href="codes_promo.php" 
                   class="px-6 py-3 border border-gray-300 rounded text-sm font-semibold hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-8 py-3 bg-black text-white rounded text-sm font-bold uppercase tracking-widest hover:bg-gold transition">
                    Créer le Code Promo
                </button>
            </div>

        </form>

    </div>

    <script>
        // Mise à jour en temps réel de l'aperçu
        function updatePreview() {
            const code = document.querySelector('input[name="code"]').value.toUpperCase() || '-';
            const type = document.querySelector('select[name="type"]').value;
            const valeur = document.querySelector('input[name="valeur"]').value;
            
            document.getElementById('previewCode').textContent = code;
            
            if (valeur) {
                if (type === 'pourcentage') {
                    document.getElementById('previewReduction').textContent = '-' + valeur + '%';
                } else {
                    document.getElementById('previewReduction').textContent = '-' + parseFloat(valeur).toLocaleString('fr-FR') + ' €';
                }
            } else {
                document.getElementById('previewReduction').textContent = '-';
            }
        }
        
        function updateReductionPlaceholder(select) {
            const input = document.getElementById('valeurInput');
            if (select.value === 'pourcentage') {
                input.placeholder = 'Ex: 10 (pour 10%)';
                input.max = '100';
            } else {
                input.placeholder = 'Ex: 50 (pour 50€)';
                input.removeAttribute('max');
            }
            updatePreview();
        }
        
        // Événements
        document.querySelector('input[name="code"]').addEventListener('input', updatePreview);
        document.querySelector('select[name="type"]').addEventListener('change', updatePreview);
        document.querySelector('input[name="valeur"]').addEventListener('input', updatePreview);
        
        // Initialiser l'aperçu
        updatePreview();
    </script>

</body>
</html>