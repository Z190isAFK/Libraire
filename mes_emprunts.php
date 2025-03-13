<?php
session_start();

// Vérifier si l'utilisateur est connecté et a le rôle "lecteur"
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'lecteur') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "librairie");
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Récupérer les livres empruntés par l'utilisateur
$sql = "SELECT p.id AS pret_id, p.livre_id, p.date_debut, p.date_fin, l.titre, l.serie, l.numero, l.image 
        FROM prets p 
        JOIN livres l ON p.livre_id = l.id 
        WHERE p.utilisateur_id = ? AND p.date_fin IS NULL";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Gestion du rendu d'un livre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pret_id'])) {
    $pret_id = (int)$_POST['pret_id'];

    // Vérifier si le prêt appartient à l'utilisateur
    $stmt = $conn->prepare("SELECT livre_id FROM prets WHERE id = ? AND utilisateur_id = ? AND date_fin IS NULL");
    $stmt->bind_param("ii", $pret_id, $user_id);
    $stmt->execute();
    $result_pret = $stmt->get_result();

    if ($result_pret->num_rows === 1) {
        $pret = $result_pret->fetch_assoc();
        $livre_id = $pret['livre_id'];

        // Mettre à jour la date de fin du prêt
        $date_fin = date("Y-m-d");
        $stmt = $conn->prepare("UPDATE prets SET date_fin = ? WHERE id = ?");
        $stmt->bind_param("si", $date_fin, $pret_id);
        $stmt->execute();

        // Rendre le livre disponible
        $stmt = $conn->prepare("UPDATE livres SET disponible = 1 WHERE id = ?");
        $stmt->bind_param("i", $livre_id);
        $stmt->execute();

        // Rafraîchir la page après le rendu
        header("Location: mes_emprunts.php?success=" . urlencode("Livre rendu avec succès !"));
        exit();
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Emprunts - Librairie en Ligne</title>
    <link rel="stylesheet" href="../Librairie/CSS/styles">
</head>
<body>
    <!-- Inclure la navbar -->
    <?php include '../Librairie/navbar.php'; ?>

    <div class="my-loans">
        <h1>Mes Emprunts</h1>
        <?php if (isset($_GET['success'])) : ?>
            <p class="success"><?php echo htmlspecialchars($_GET['success']); ?></p>
        <?php endif; ?>
        <?php if ($result->num_rows > 0) : ?>
            <div class="book-grid">
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <div class="book">
                        <?php if (!empty($row['image'])) : ?>
                            <img src="images/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['titre']); ?>" class="book-image">
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($row['titre']); ?></h3>
                        <p>Série : <?php echo htmlspecialchars($row['serie']); ?></p>
                        <p>Numéro : <?php echo htmlspecialchars($row['numero']); ?></p>
                        <p>Emprunté le : <?php echo htmlspecialchars($row['date_debut']); ?></p>
                        <form method="POST" action="mes_emprunts.php">
                            <input type="hidden" name="pret_id" value="<?php echo $row['pret_id']; ?>">
                            <button type="submit" class="return-button">Rendre</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <p>Vous n'avez aucun livre emprunté actuellement.</p>
        <?php endif; ?>
    </div>
</body>
</html>