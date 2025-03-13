<?php
session_start(); // Démarre la session pour pouvoir la manipuler

// Détruire toutes les données de la session
session_unset(); // Supprime toutes les variables de session
session_destroy(); // Détruit la session complètement

// Rediriger vers l'index
header("Location: index.php"); // Redirige vers index.php
exit(); // Arrête l'exécution du script
?>