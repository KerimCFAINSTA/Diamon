<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // À PERSONNALISER avec tes identifiants
    if ($username === 'admin93170"&' && $password === 'azerti93170"&') {
        $_SESSION['admin'] = true;
        header('Location: demandes_vente.php');
        exit();
    } else {
        $erreur = "Identifiants incorrects";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | DIAMON</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h1 class="text-2xl font-bold mb-6 text-center">DIAMON Admin</h1>
        
        <?php if (isset($erreur)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm">
                <?= htmlspecialchars($erreur) ?>
            </div>
        <?php endif; ?>
        
        <form method="post" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold mb-2">Username</label>
                <input type="text" name="username" required class="w-full px-4 py-2 border rounded">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 border rounded">
            </div>
            <button type="submit" class="w-full bg-black text-white py-3 rounded font-semibold hover:bg-gray-800">
                Se Connecter
            </button>
        </form>
    </div>
</body>
</html>