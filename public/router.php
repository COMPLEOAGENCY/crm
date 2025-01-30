<?php
// router.php
chdir(__DIR__); // Définir le répertoire de travail comme étant le dossier public

if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
} else { 
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    include __DIR__ . '/index.php';
}
