<?php
session_start(); // Démarre la session pour vérifier si un utilisateur est déjà connecté

// Vérifier si l'utilisateur est déjà connecté (si oui, on le redirige vers l'index)
if (isset($_SESSION['role'])) {
    header("Location: index.php"); // Redirige vers index.php
    exit(); // Arrête l'exécution du script
}

// Connexion à la base de données (paramètres : hôte, utilisateur, mot de passe, nom de la base)
$conn = new mysqli("localhost", "root", "", "librairie");
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error); // Affiche une erreur si la connexion échoue
}

// Variable pour stocker un message d'erreur ou de succès à afficher à l'utilisateur
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Vérifie si le formulaire a été soumis (méthode POST)
    $username = trim($_POST['username']); // Récupère le nom d'utilisateur et supprime les espaces inutiles
    $password = trim($_POST['password']); // Récupère le mot de passe et supprime les espaces inutiles

    // Vérifier que les champs ne sont pas vides
    if (empty($username) || empty($password)) {
        $message = "Veuillez remplir tous les champs."; // Message d'erreur si un champ est vide
    } else {
        // Vérifier si le nom d'utilisateur existe déjà dans la table utilisateurs
        $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE username = ?"); // Prépare une requête SQL
        $stmt->bind_param("s", $username); // Lie le paramètre username (s pour string)
        $stmt->execute(); // Exécute la requête
        $result = $stmt->get_result(); // Récupère le résultat

        if ($result->num_rows > 0) { // Si un utilisateur avec ce nom existe déjà
            $message = "Ce nom d'utilisateur est déjà pris."; // Message d'erreur
        } else {
            // Hacher le mot de passe pour la sécurité (ne jamais stocker en clair)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'lecteur'; // Définit le rôle par défaut pour un nouvel utilisateur (lecteur)

            // Insérer le nouvel utilisateur dans la table utilisateurs
            $stmt = $conn->prepare("INSERT INTO utilisateurs (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $role); // Lie les paramètres (s pour string)
            if ($stmt->execute()) { // Si l'insertion réussit
                $message = "Inscription réussie ! Vous pouvez maintenant vous connecter."; // Message de succès
            } else {
                $message = "Erreur lors de l'inscription."; // Message d'erreur si l'insertion échoue
            }
        }
        $stmt->close(); // Ferme la requête préparée
    }
}

$conn->close(); // Ferme la connexion à la base de données
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Librairie en Ligne</title>
    <link rel="stylesheet" href="../Librairie/CSS/styles"> <!-- Lien vers le fichier CSS -->
</head>
<body>
    <?php include 'navbar.php'; ?> <!-- Inclut la barre de navigation -->

    <h1>Inscription</h1> <!-- Titre de la page -->
    <?php if ($message) : ?> <!-- Si un message existe (succès ou erreur) -->
        <p><?php echo htmlspecialchars($message); ?></p> <!-- Affiche le message (protégé contre XSS) -->
    <?php endif; ?>
    <form method="POST" action="register.php"> <!-- Formulaire d'inscription -->
        <label for="username">Nom d'utilisateur :</label> <!-- Étiquette pour le champ username -->
        <input type="text" id="username" name="username" required><br> <!-- Champ pour le nom d'utilisateur -->
        <label for="password">Mot de passe :</label> <!-- Étiquette pour le champ mot de passe -->
        <input type="password" id="password" name="password" required><br> <!-- Champ pour le mot de passe -->
        <button type="submit">S'inscrire</button> <!-- Bouton pour soumettre le formulaire -->
    </form>
    <p>Déjà un compte ? <a href="login.php">Connectez-vous</a>.</p> <!-- Lien pour ceux qui ont déjà un compte -->
</body>
</html>