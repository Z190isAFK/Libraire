<?php
session_start();

// Vérifier si l'utilisateur est connecté et a le rôle approprié
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['lecteur', 'administrateur'])) {
    header("Location: login.php");
    exit();
}   

// Vérifier si un ID de livre est passé en paramètre
if (!isset($_POST['livre_id']) || !is_numeric($_POST['livre_id'])) {
    header("Location: index.php");
    exit();
}

$livre_id = (int)$_POST['livre_id'];
$utilisateur_id = $_SESSION['user_id'];

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "librairie");
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Vérifier si le livre est disponible
$stmt = $conn->prepare("SELECT disponible FROM livres WHERE id = ?");
$stmt->bind_param("i", $livre_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: index.php");
    exit();
}

$livre = $result->fetch_assoc();
if (!$livre['disponible']) {
    // Rediriger avec un message d'erreur si le livre n'est pas disponible
    header("Location: livre.php?id=$livre_id&error=" . urlencode("Ce livre est déjà emprunté."));
    exit();
}

// Mettre à jour le statut du livre (disponible = 0)
$stmt = $conn->prepare("UPDATE livres SET disponible = 0 WHERE id = ?");
$stmt->bind_param("i", $livre_id);
$stmt->execute();

// Enregistrer l'emprunt dans la table prets
$date_debut = date("Y-m-d"); // Date actuelle au format YYYY-MM-DD
$stmt = $conn->prepare("INSERT INTO prets (utilisateur_id, livre_id, date_debut) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $utilisateur_id, $livre_id, $date_debut);
$stmt->execute();

$stmt->close();
$conn->close();

// Rediriger vers la page du livre avec un message de succès
header("Location: livre.php?id=$livre_id&success=" . urlencode("Livre emprunté avec succès !"));
exit();
?>