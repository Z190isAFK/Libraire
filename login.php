<?php
session_start(); // Démarre la session pour gérer l'état de connexion

// Vérifier si l'utilisateur est déjà connecté (si oui, on le redirige vers l'index)
if (isset($_SESSION['role'])) {
    header("Location: index.php"); // Redirige vers index.php
    exit(); // Arrête l'exécution du script
}

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "librairie");
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error); // Affiche une erreur si la connexion échoue
}

$message = ''; // Variable pour stocker un message d'erreur ou de succès
if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Vérifie si le formulaire a été soumis
    $username = trim($_POST['username']); // Récupère le nom d'utilisateur
    $password = trim($_POST['password']); // Récupère le mot de passe

    // Vérifier que les champs ne sont pas vides
    if (empty($username) || empty($password)) {
        $message = "Veuillez remplir tous les champs."; // Message d'erreur si un champ est vide
    } else {
        // Vérifier si l'utilisateur existe dans la table utilisateurs
        $stmt = $conn->prepare("SELECT id, username, password, role FROM utilisateurs WHERE username = ?");
        $stmt->bind_param("s", $username); // Lie le paramètre username
        $stmt->execute(); // Exécute la requête
        $result = $stmt->get_result(); // Récupère le résultat

        if ($result->num_rows === 1) { // Si exactement un utilisateur est trouvé
            $user = $result->fetch_assoc(); // Récupère les données de l'utilisateur
            // Vérifier si le mot de passe est correct
            if (password_verify($password, $user['password'])) {
                // Stocker les informations dans la session
                $_SESSION['user_id'] = $user['id']; // ID de l'utilisateur
                $_SESSION['username'] = $user['username']; // Nom d'utilisateur
                $_SESSION['role'] = $user['role']; // Rôle (lecteur ou administrateur)
                header("Location: index.php"); // Redirige vers index.php
                exit(); // Arrête l'exécution du script
            } else {
                $message = "Nom d'utilisateur ou mot de passe incorrect."; // Mot de passe incorrect
            }
        } else {
            $message = "Nom d'utilisateur ou mot de passe incorrect."; // Utilisateur non trouvé
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
    <title>Connexion - Librairie en Ligne</title>
    <link rel="stylesheet" href="../Librairie/CSS/styles"> <!-- Lien vers le fichier CSS -->
</head>
<body>
    <?php include 'navbar.php'; ?> <!-- Inclut la barre de navigation -->

    <h1>Connexion</h1> <!-- Titre de la page -->
    <?php if ($message) : ?> <!-- Si un message existe (succès ou erreur) -->
        <p><?php echo htmlspecialchars($message); ?></p> <!-- Affiche le message -->
    <?php endif; ?>
    <form method="POST" action="login.php"> <!-- Formulaire de connexion -->
        <label for="username">Nom d'utilisateur :</label> <!-- Étiquette pour le champ username -->
        <input type="text" id="username" name="username" required><br> <!-- Champ pour le nom d'utilisateur -->
        <label for="password">Mot de passe :</label> <!-- Étiquette pour le champ mot de passe -->
        <input type="password" id="password" name="password" required><br> <!-- Champ pour le mot de passe -->
        <button type="submit">Se connecter</button> <!-- Bouton pour soumettre le formulaire -->
    </form>
    <p>Pas de compte ? <a href="register.php">Inscrivez-vous</a>.</p> <!-- Lien pour s'inscrire -->
</body>
</html>