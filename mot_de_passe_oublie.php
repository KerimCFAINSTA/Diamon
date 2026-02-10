<?php 
$page_title = "Mot de Passe Oublié | DIAMON";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/email_config.php';

$success = false;
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "Veuillez entrer une adresse email valide.";
    } else {
        // Vérifier si l'email existe
        $stmt = $pdo->prepare("SELECT id, nom, prenom FROM client WHERE email = ?");
        $stmt->execute([$email]);
        $client = $stmt->fetch();
        
        if ($client) {
            // Générer un token unique
            $token = bin2hex(random_bytes(32));
            $expire_at = date('Y-m-d H:i:s', time() + 3600); // 1 heure
            
            // Sauvegarder le token
            $stmt = $pdo->prepare("INSERT INTO password_reset (email, token, expire_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expire_at]);
            
            // Envoyer l'email
            if (EmailService::envoyerResetPassword($email, $token, $client['prenom'] . ' ' . $client['nom'])) {
                $success = true;
            } else {
                $erreur = "Erreur lors de l'envoi de l'email. Veuillez réessayer.";
            }
        } else {
            // Pour la sécurité, on affiche le même message même si l'email n'existe pas
            $success = true;
        }
    }
}
?>

<main class="pt-32 pb-24">
    <div class="max-w-md mx-auto px-6">
        
        <div class="text-center mb-12">
            <h1 class="text-4xl font-serif italic mb-4">Mot de Passe Oublié</h1>
            <p class="text-gray-500 text-sm">Entrez votre email pour recevoir un lien de réinitialisation</p>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-100 border-2 border-green-400 text-green-800 p-8 rounded-lg text-center">
                <svg class="w-16 h-16 text-green-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <h2 class="text-xl font-bold mb-3">Email Envoyé !</h2>
                <p class="mb-4">
                    Si un compte existe avec cet email, vous recevrez un lien de réinitialisation sous quelques minutes.
                </p>
                <p class="text-sm text-gray-600">
                    Pensez à vérifier vos spams si vous ne le trouvez pas.
                </p>
            </div>
        <?php else: ?>
            
            <?php if ($erreur): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded mb-8">
                    <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-6">
                
                <div>
                    <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Email</label>
                    <input type="email" 
                           name="email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           required
                           placeholder="votre-email@exemple.com"
                           class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                </div>

                <button type="submit" 
                        class="w-full bg-black text-white py-5 text-xs font-bold uppercase tracking-widest hover:bg-gold transition duration-300">
                    Envoyer le Lien de Réinitialisation
                </button>

                <p class="text-center text-sm text-gray-600">
                    Vous vous souvenez de votre mot de passe ? 
                    <a href="<?= $base_url ?>/connexion.php" class="text-gold hover:underline font-semibold">
                        Se connecter
                    </a>
                </p>
            </form>
        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>