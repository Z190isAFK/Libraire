<?php
session_start();

// Vérifier si l'utilisateur est connecté et son rôle
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;

// Vérifier si un ID de livre est passé en paramètre
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$livre_id = (int)$_GET['id'];

// Récupérer les messages de succès ou d'erreur
$success = isset($_GET['success']) ? $_GET['success'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "librairie");
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Récupérer les détails du livre
$stmt = $conn->prepare("SELECT id, titre, serie, numero, disponible, image, description FROM livres WHERE id = ?");
$stmt->bind_param("i", $livre_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: index.php");
    exit();
}

$livre = $result->fetch_assoc();

// Vérifier si le livre est emprunté et par qui (facultatif, pour information)
$emprunteur = null;
if (!$livre['disponible']) {
    $stmt = $conn->prepare("SELECT u.username 
                            FROM prets p 
                            JOIN utilisateurs u ON p.utilisateur_id = u.id 
                            WHERE p.livre_id = ? AND p.date_fin IS NULL");
    $stmt->bind_param("i", $livre_id);
    $stmt->execute();
    $result_emprunteur = $stmt->get_result();
    if ($result_emprunteur->num_rows > 0) {
        $emprunteur = $result_emprunteur->fetch_assoc()['username'];
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($livre['titre']); ?> - Librairie en Ligne</title>
    <link rel="stylesheet" href="../Librairie/CSS/styles">
</head>
<body>
    <!-- Inclure la navbar -->
    <?php include '../Librairie/navbar.php'; ?>

    <div class="book-details">
        <h1><?php echo htmlspecialchars($livre['titre']); ?></h1>
        <?php if ($success) : ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if ($error) : ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (!empty($livre['image'])) : ?>
            <img src="images/<?php echo htmlspecialchars($livre['image']); ?>" alt="<?php echo htmlspecialchars($livre['titre']); ?>" class="book-image">
        <?php endif; ?>
        <p><strong>Série :</strong> <?php echo htmlspecialchars($livre['serie']); ?></p>
        <p><strong>Numéro :</strong> <?php echo htmlspecialchars($livre['numero']); ?></p>
        <p><strong>Statut :</strong> 
            <span class="<?php echo $livre['disponible'] ? 'status-available' : 'status-borrowed'; ?>">
                <?php echo $livre['disponible'] ? 'Disponible' : 'Emprunté'; ?>
            </span>
            <?php if (!$livre['disponible'] && $emprunteur) : ?>
                (par <?php echo htmlspecialchars($emprunteur); ?>)
            <?php endif; ?>
        </p>
        <p><strong>Description :</strong> <?php echo htmlspecialchars($livre['description']); ?></p>

        <?php if ($role && $livre['disponible']) : ?>
            <form method="POST" action="emprunter.php">
                <input type="hidden" name="livre_id" value="<?php echo $livre['id']; ?>">
                <button type="submit">Emprunter</button>
            </form>
        <?php elseif (!$role) : ?>
            <p>Connectez-vous pour emprunter ce livre. <a href="login.php">Connexion</a> | <a href="register.php">Inscription</a></p>
        <?php elseif (!$livre['disponible']) : ?>
            <p>Ce livre est actuellement indisponible.</p>
        <?php endif; ?>
    </div>
</body>
</html>