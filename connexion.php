<?php 
$page_title = "Connexion | DIAMON";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/models/Client.php';

$erreur = '';
$redirect = $_GET['redirect'] ?? 'compte';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    
    if (empty($email) || empty($mot_de_passe)) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        $client = Client::connexion($email, $mot_de_passe);
        
        if ($client) {
            $_SESSION['client_id'] = $client['id'];
            $_SESSION['client_nom'] = $client['nom'];
            $_SESSION['client_prenom'] = $client['prenom'];
            $_SESSION['client_email'] = $client['email'];
            
            // Redirection
            if ($redirect === 'panier') {
                header('Location: views/panier.php');
            } else {
                header('Location: compte.php');
            }
            exit();
        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    }
}
?>

<main class="pt-32 pb-24">
    <div class="max-w-md mx-auto px-6">
        
        <div class="text-center mb-12">
            <h1 class="text-4xl font-serif italic mb-4">Connexion</h1>
            <p class="text-gray-500 text-sm">Accédez à votre espace personnel</p>
        </div>

        <?php if (isset($_SESSION['success_inscription'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded mb-8">
                <?= htmlspecialchars($_SESSION['success_inscription']) ?>
                <?php unset($_SESSION['success_inscription']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($erreur)): ?>
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
                       class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
            </div>

            <div>
                <label class="block text-xs uppercase tracking-widest font-semibold mb-2">Mot de passe</label>
                <input type="password" 
                       name="mot_de_passe" 
                       required
                       class="w-full px-4 py-3 border border-gray-300 focus:border-gold focus:outline-none">
            </div>

            <div class="flex items-center justify-between text-sm">
    <label class="flex items-center">
        <input type="checkbox" name="remember" class="mr-2">
        <span class="text-gray-600">Se souvenir de moi</span>
    </label>
    <a href="<?= $base_url ?>/mot_de_passe_oublie.php" class="text-gold hover:underline">
        Mot de passe oublié ?
    </a>
</div>

            <button type="submit" 
                    class="w-full bg-black text-white py-5 text-xs font-bold uppercase tracking-widest hover:bg-gold transition duration-300">
                Se Connecter
            </button>

            <p class="text-center text-sm text-gray-600">
                Pas encore de compte ? 
                <a href="<?= $base_url ?>/inscription.php" class="text-gold hover:underline font-semibold">
                    S'inscrire
                </a>
            </p>
        </form>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>