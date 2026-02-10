<?php
// Autoloader manuel pour PHPMailer
spl_autoload_register(function ($class) {
    // Chemin vers PHPMailer
    $prefix = 'PHPMailer\\PHPMailer\\';
    $base_dir = __DIR__ . '/phpmailer/phpmailer/src/';

    // Vérifie si la classe utilise le namespace PHPMailer
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Obtient le nom de la classe relatif
    $relative_class = substr($class, $len);

    // Remplace le namespace par le chemin du fichier
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Si le fichier existe, l'inclure
    if (file_exists($file)) {
        require $file;
    }
});