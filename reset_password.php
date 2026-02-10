<?php 
$page_title = "Nouveau Mot de Passe | DIAMON";
require_once __DIR__ . '/includes/header.php';

$token = $_GET['token'] ?? '';
$erreurs = [];
$success = false;
$token_valide = false;

// Vérifier le token
if ($token) {
    $stmt = $pdo->prepare("
        SELECT pr.*, c.id as client_id, c.nom, c.prenom 
        FROM password_reset pr
        JOIN client c ON pr.email = c.email
        WHERE pr.token = ? AND pr.used = 0 AND pr.expire_at > NOW()
    ");
    $stmt->execute([$token]);
    $reset_data = $stmt->fetch();
    
    if ($reset_data) {
        $token_valide = true;
    } else {
        $erreurs[] = "Ce lien est invalide ou a expiré.";
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valide) {
    $nouveau_mdp = $_POST['nouveau_mdp'] ?? '';
    $confirmer_mdp = $_POST['confirmer_mdp'] ?? '';
    
    if (empty($nouveau_mdp)) {
        $erreurs[] = "Le mot de passe est requis.";
    } elseif (strlen($nouveau_mdp) < 8) {
        $erreurs[] = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($nouveau_mdp !== $confirmer_mdp) {
        $erreurs[] = "Les mots de passe ne correspondent pas.";
    }
    
    if (empty($erreurs)) {
        // Mettre à jour le mot de passe
        $mdp_hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE client SET mot_de_passe = ? WHERE id = ?");
        $stmt->execute([$mdp_hash, $reset_data['client_id']]);
        
        // Marquer le token comme utilisé
        $stmt = $pdo->prepare("UPDATE password_reset SET used = 1 WHERE token = ?");
        $stmt->execute([$token]);
        
        $success = true;
    }
}
?>

<main class="pt-32 pb-24">
    <div class="max-w-md mx-auto px-6">
        
        <div class="text-center mb-12">
            <h1 class="text-4xl font-serif italic mb-4">Nouveau Mot de Passe</h1>
            <p class="text-gray-500 text-sm">Choisissez un nouveau mot de passe sécurisé</p>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-100 border-2 border-green-400 text-green-800 p-8 rounded-lg text-center">
                <svg class="w-16 h-16 text-green-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h2 class="text-xl font-bold mb-3">Mot de Passe Réinitialisé !</h2>
                <p class="mb-6">
                    Votre mot de passe a été modifié avec succès. Vous pouvez maintenant vous connecter.
                </p>
                <a href="<?= $base_url ?>/connexion.php" 
                   class="inline-block bg-black text-white px-8 py-3 text-xs font-bold uppercase tracking-widest hover:bg-gold transition">
                    Se Connecter
                </a>
            </div>
        <?php elseif ($token_valide): ?>
            
            <?php if (!empty($erreurs)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded mb-8">
                    <ul class="list-disc list-inside">
                        <?php foreach ($erreurs as $erreur): ?>
                            <li><?= htmlspecialchars($erreur) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-6">
                
                <div>
                    <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Nouveau Mot de Passe</label>
                    <input type="password" 
                           name="nouveau_mdp" 
                           required
                           minlength="8"
                           placeholder="Minimum 8 caractères"
                           class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Confirmer le Mot de Passe</label>
                    <input type="password" 
                           name="confirmer_mdp" 
                           required
                           minlength="8"
                           class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                </div>

                <button type="submit" 
                        class="w-full bg-black text-white py-5 text-xs font-bold uppercase tracking-widest hover:bg-gold transition duration-300">
                    Réinitialiser le Mot de Passe
                </button>
            </form>
            
        <?php else: ?>
            <div class="bg-red-100 border-2 border-red-400 text-red-800 p-8 rounded-lg text-center">
                <svg class="w-16 h-16 text-red-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h2 class="text-xl font-bold mb-3">Lien Invalide</h2>
                <p class="mb-6">
                    Ce lien de réinitialisation est invalide ou a expiré.
                </p>
                <a href="<?= $base_url ?>/mot_de_passe_oublie.php" 
                   class="inline-block bg-black text-white px-8 py-3 text-xs font-bold uppercase tracking-widest hover:bg-gold transition">
                    Demander un Nouveau Lien
                </a>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>