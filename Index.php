<?php
// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "librairie");
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Récupérer les livres
$sql = "SELECT id, titre, serie, numero, disponible, image FROM livres ORDER BY serie, numero";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Librairie en Ligne</title>
    <link rel="stylesheet" href="../Librairie/CSS/styles">
</head>
<body>
    <!-- Inclure la navbar -->
    <?php include '../Librairie/navbar.php'; ?>

    <!-- Message de bienvenue selon le rôle -->
    <p>
        <?php if (!$role) : ?>
            Bienvenue, visiteur ! Connectez-vous pour emprunter des livres.
            <a href="login.php">Connexion</a> | <a href="register.php">Inscription</a>
        <?php elseif ($role === 'lecteur') : ?>
            Bienvenue, Lecteur ! Découvrez les livres ci-dessous.
        <?php elseif ($role === 'administrateur') : ?>
            Bienvenue, Administrateur ! Découvrez les livres ci-dessous.
        <?php endif; ?>
    </p>

    <!-- Grille des livres -->
    <div class="book-grid">
        <?php if ($result->num_rows > 0) : ?>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <div class="book">
                    <?php if (!empty($row['image'])) : ?>
                        <img src="images/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['titre']); ?>" class="book-image">
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($row['titre']); ?></h3>
                    <p>Série : <?php echo htmlspecialchars($row['serie']); ?></p>
                    <p>Numéro : <?php echo htmlspecialchars($row['numero']); ?></p>
                    <p>Statut : <span class="<?php echo $row['disponible'] ? 'status-available' : 'status-borrowed'; ?>">
                        <?php echo $row['disponible'] ? 'Disponible' : 'Emprunté'; ?>
                    </span></p>
                    <a href="livre.php?id=<?php echo $row['id']; ?>" class="discover-button">Découvrir</a>
                </div>
            <?php endwhile; ?>
        <?php else : ?>
            <p>Aucun livre trouvé.</p>
        <?php endif; ?>
    </div>

    <?php $conn->close(); ?>
</body>
</html>