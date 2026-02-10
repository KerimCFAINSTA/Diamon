<?php 
$page_title = "Inscription | DIAMON";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/models/Client.php';

$erreurs = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $mot_de_passe_confirm = $_POST['mot_de_passe_confirm'] ?? '';
    
    // Validation
    if (empty($nom)) $erreurs[] = "Le nom est requis.";
    if (empty($prenom)) $erreurs[] = "Le prénom est requis.";
    if (empty($email)) $erreurs[] = "L'email est requis.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "L'email n'est pas valide.";
    if (empty($mot_de_passe)) $erreurs[] = "Le mot de passe est requis.";
    if (strlen($mot_de_passe) < 8) $erreurs[] = "Le mot de passe doit contenir au moins 8 caractères.";
    if ($mot_de_passe !== $mot_de_passe_confirm) $erreurs[] = "Les mots de passe ne correspondent pas.";
    
    // Vérifier si l'email existe déjà
    if (Client::emailExiste($email)) {
        $erreurs[] = "Cet email est déjà utilisé.";
    }
    
    // Si pas d'erreurs, créer le compte
    if (empty($erreurs)) {
        $resultat = Client::creer($nom, $prenom, $email, $mot_de_passe, $telephone);
        
        if ($resultat) {
            $success = true;
            $_SESSION['success_inscription'] = "Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.";
            header('Location: connexion.php');
            exit();
        } else {
            $erreurs[] = "Une erreur est survenue lors de la création du compte.";
        }
    }
}
?>

<main class="pt-32 pb-24">
    <div class="max-w-md mx-auto px-6">
        
        <div class="text-center mb-12">
            <h1 class="text-4xl font-serif italic mb-4">Créer un Compte</h1>
            <p class="text-gray-500 text-sm">Rejoignez la communauté DIAMON</p>
        </div>

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
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Prénom *</label>
                    <input type="text" 
                           name="prenom" 
                           value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"
                           required
                           class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Nom *</label>
                    <input type="text" 
                           name="nom" 
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                           required
                           class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Email *</label>
                <input type="email" 
                       name="email" 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required
                       class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
            </div>

            <div>
                <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Téléphone</label>
                <input type="tel" 
                       name="telephone" 
                       value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>"
                       class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
            </div>

            <div>
                <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Mot de passe *</label>
                <input type="password" 
                       name="mot_de_passe" 
                       required
                       minlength="8"
                       class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
                <p class="text-xs text-gray-500 mt-1">Minimum 8 caractères</p>
            </div>

            <div>
                <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Confirmer le mot de passe *</label>
                <input type="password" 
                       name="mot_de_passe_confirm" 
                       required
                       minlength="8"
                       class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
            </div>

            <button type="submit" 
                    class="w-full bg-black text-white py-5 text-xs font-bold uppercase tracking-widest hover:bg-gold transition duration-300">
                Créer mon Compte
            </button>

            <p class="text-center text-sm text-gray-600">
                Vous avez déjà un compte ? 
                <a href="<?= $base_url ?>/connexion.php" class="text-gold hover:underline font-semibold">
                    Se connecter
                </a>
            </p>
        </form>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>