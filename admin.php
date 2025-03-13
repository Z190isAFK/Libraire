<?php
session_start();

// Vérifier si l'utilisateur est connecté et a le rôle administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "librairie");
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Gestion du rendu d'un livre par l'administrateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pret_id'])) {
    $pret_id = (int)$_POST['pret_id'];

    // Récupérer le livre_id associé au prêt
    $stmt = $conn->prepare("SELECT livre_id FROM prets WHERE id = ? AND date_fin IS NULL");
    $stmt->bind_param("i", $pret_id);
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
        header("Location: admin.php?success=" . urlencode("Livre rendu avec succès !"));
        exit();
    }
}

// Gestion de l'effacement de l'historique d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_history']) && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];

    // Supprimer les emprunts rendus (date_fin non NULL) pour cet utilisateur
    $stmt = $conn->prepare("DELETE FROM prets WHERE utilisateur_id = ? AND date_fin IS NOT NULL");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Rafraîchir la page après l'effacement
    header("Location: admin.php?success=" . urlencode("Historique effacé avec succès pour cet utilisateur !"));
    exit();
}

// Gestion de l'ajout d'un nouveau livre avec upload d'image
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $titre = $conn->real_escape_string($_POST['titre']);
    $serie = $conn->real_escape_string($_POST['serie']); // La série vient maintenant d'un <select>
    $numero = (int)$_POST['numero'];
    $description = $conn->real_escape_string($_POST['description']);
    $disponible = isset($_POST['disponible']) ? 1 : 0;

    // Gestion de l'upload de l'image
    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "../Librairie/Images";
        
        // Vérifier si le dossier existe et est accessible en écriture
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }
        if (!is_writable($upload_dir)) {
            header("Location: admin.php?error=" . urlencode("Le dossier images/ n'est pas accessible en écriture."));
            exit();
        }

        $image_name = uniqid() . "_" . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $image_name;

        // Vérifier si le fichier est une image
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($image_file_type, $allowed_types)) {
            header("Location: admin.php?error=" . urlencode("Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés."));
            exit();
        }

        // Vérifier la taille du fichier (limite à 5MB par exemple)
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            header("Location: admin.php?error=" . urlencode("L'image dépasse la taille maximale autorisée (5MB)."));
            exit();
        }

        // Tenter de déplacer le fichier uploadé
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            header("Location: admin.php?error=" . urlencode("Erreur lors de l'upload de l'image : impossible de déplacer le fichier."));
            exit();
        }
    } else {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => "L'image dépasse la taille maximale autorisée par le serveur (voir upload_max_filesize dans php.ini).",
            UPLOAD_ERR_FORM_SIZE => "L'image dépasse la taille maximale autorisée par le formulaire.",
            UPLOAD_ERR_PARTIAL => "L'image a été partiellement uploadée.",
            UPLOAD_ERR_NO_FILE => "Aucune image n'a été uploadée.",
            UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant.",
            UPLOAD_ERR_CANT_WRITE => "Impossible d'écrire le fichier sur le disque.",
            UPLOAD_ERR_EXTENSION => "Une extension PHP a arrêté l'upload."
        ];
        $error_message = isset($upload_errors[$_FILES['image']['error']]) 
            ? $upload_errors[$_FILES['image']['error']] 
            : "Erreur inconnue lors de l'upload.";
        header("Location: admin.php?error=" . urlencode($error_message));
        exit();
    }

    // Insérer le livre dans la base de données
    $stmt = $conn->prepare("INSERT INTO livres (titre, serie, numero, description, image, disponible) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisdi", $titre, $serie, $numero, $description, $image_name, $disponible);
    if (!$stmt->execute()) {
        header("Location: admin.php?error=" . urlencode("Erreur lors de l'insertion dans la base de données : " . $stmt->error));
        exit();
    }
    $stmt->close();

    header("Location: admin.php?success=" . urlencode("Livre ajouté avec succès !"));
    exit();
}

// Gestion de la suppression d'un livre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book']) && isset($_POST['livre_id'])) {
    $livre_id = (int)$_POST['livre_id'];

    // Supprimer d'abord les prêts associés
    $stmt = $conn->prepare("DELETE FROM prets WHERE livre_id = ?");
    $stmt->bind_param("i", $livre_id);
    $stmt->execute();

    // Supprimer le livre
    $stmt = $conn->prepare("SELECT image FROM livres WHERE id = ?");
    $stmt->bind_param("i", $livre_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $livre = $result->fetch_assoc();
        $image_path = "images/" . $livre['image'];
        if (file_exists($image_path)) {
            unlink($image_path); // Supprimer l'image du serveur
        }
    }

    $stmt = $conn->prepare("DELETE FROM livres WHERE id = ?");
    $stmt->bind_param("i", $livre_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin.php?success=" . urlencode("Livre supprimé avec succès !"));
    exit();
}

// Récupérer la liste des utilisateurs avec leurs emprunts
$sql_users = "SELECT u.id, u.username, u.email, p.id AS pret_id, p.livre_id, l.titre, p.date_debut, p.date_fin 
              FROM utilisateurs u 
              LEFT JOIN prets p ON u.id = p.utilisateur_id 
              LEFT JOIN livres l ON p.livre_id = l.id 
              ORDER BY u.username";
$result_users = $conn->query($sql_users);

// Récupérer la liste des livres pour la suppression
$sql_livres = "SELECT id, titre, serie, numero, disponible FROM livres ORDER BY serie, numero";
$result_livres = $conn->query($sql_livres);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Panneau Administrateur - Librairie en Ligne</title>
    <link rel="stylesheet" href="../Librairie/CSS/styles">
</head>
<body>
    <!-- Inclure la navbar -->
    <?php include '../Librairie/navbar.php'; ?>

    <div class="admin-panel">
        <h1>Panneau Administrateur</h1>
        <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?> ! Voici la liste des utilisateurs, leurs emprunts, et la gestion des livres.</p>
        <?php if (isset($_GET['success'])) : ?>
            <p class="success"><?php echo htmlspecialchars($_GET['success']); ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['error'])) : ?>
            <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <!-- Section pour les utilisateurs et leurs emprunts -->
        <h2>Liste des utilisateurs et emprunts</h2>
        <?php if ($result_users->num_rows > 0) : ?>
            <table>
                <thead>
                    <tr>
                        <th>Nom d'utilisateur</th>
                        <th>E-mail</th>
                        <th>Livre emprunté</th>
                        <th>Date de début</th>
                        <th>Date de fin</th>
                        <th>Action (Rendre)</th>
                        <th>Action (Historique)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users = [];
                    $result_users->data_seek(0); // Réinitialiser le pointeur des résultats
                    while ($row = $result_users->fetch_assoc()) {
                        $user_id = $row['id'];
                        if (!isset($users[$user_id])) {
                            $users[$user_id] = [
                                'username' => $row['username'],
                                'email' => $row['email'],
                                'livres' => []
                            ];
                        }
                        if ($row['livre_id']) {
                            $users[$user_id]['livres'][] = [
                                'pret_id' => $row['pret_id'],
                                'titre' => $row['titre'],
                                'date_debut' => $row['date_debut'],
                                'date_fin' => $row['date_fin']
                            ];
                        }
                    }

                    foreach ($users as $user_id => $user) :
                    ?>
                        <tr>
                            <td rowspan="<?php echo count($user['livres']) ?: 1; ?>">
                                <?php echo htmlspecialchars($user['username']); ?>
                            </td>
                            <td rowspan="<?php echo count($user['livres']) ?: 1; ?>">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </td>
                            <?php
                            $first = true;
                            foreach ($user['livres'] as $index => $livre) :
                                if (!$first) echo "<tr>";
                            ?>
                                <td><?php echo htmlspecialchars($livre['titre']) ?: 'Aucun livre emprunté'; ?></td>
                                <td><?php echo htmlspecialchars($livre['date_debut']) ?: '-'; ?></td>
                                <td><?php echo htmlspecialchars($livre['date_fin']) ?: '-'; ?></td>
                                <td>
                                    <?php if (!$livre['date_fin']) : ?>
                                        <form method="POST" action="admin.php">
                                            <input type="hidden" name="pret_id" value="<?php echo $livre['pret_id']; ?>">
                                            <button type="submit" class="return-button">Rendre</button>
                                        </form>
                                    <?php else : ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <?php if ($first) : ?>
                                    <td rowspan="<?php echo count($user['livres']) ?: 1; ?>">
                                        <form method="POST" action="admin.php">
                                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                            <input type="hidden" name="clear_history" value="1">
                                            <button type="submit" class="clear-history-button">Effacer l'historique</button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <?php
                                $first = false;
                            endforeach;
                            if ($first) :
                            ?>
                                <td colspan="4">Aucun livre emprunté</td>
                                <td>
                                    <form method="POST" action="admin.php">
                                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                        <input type="hidden" name="clear_history" value="1">
                                        <button type="submit" class="clear-history-button">Effacer l'historique</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>Aucun utilisateur trouvé.</p>
        <?php endif; ?>

        <!-- Section pour supprimer un livre -->
        <h2>Supprimer un livre</h2>
        <?php if ($result_livres->num_rows > 0) : ?>
            <form method="POST" action="admin.php" class="book-form">
                <label for="livre_id">Sélectionner un livre :</label>
                <select name="livre_id" id="livre_id" required>
                    <?php
                    $result_livres->data_seek(0); // Réinitialiser le pointeur des résultats
                    while ($row = $result_livres->fetch_assoc()) : ?>
                        <option value="<?php echo $row['id']; ?>">
                            <?php echo htmlspecialchars("{$row['titre']} (Série: {$row['serie']}, Numéro: {$row['numero']}, Statut: " . ($row['disponible'] ? 'Disponible' : 'Emprunté') . ")"); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="delete_book" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce livre ? Cette action est irréversible.');">Supprimer le livre</button>
            </form>
        <?php else : ?>
            <p>Aucun livre à supprimer.</p>
        <?php endif; ?>

        <!-- Section pour ajouter un livre -->
        <h2>Ajouter un nouveau livre</h2>
        <form method="POST" action="admin.php" class="book-form" enctype="multipart/form-data">
            <label for="titre">Titre :</label>
            <input type="text" name="titre" id="titre" required>
            <label for="serie">Série :</label>
            <select name="serie" id="serie" required>
                <option value="Marvel">Marvel</option>
                <option value="DC Comics">DC Comics</option>
                <option value="Invincible">Invincible</option>
            </select>
            <label for="numero">Numéro :</label>
            <input type="number" name="numero" id="numero" required>
            <label for="description">Description :</label>
            <textarea name="description" id="description" required></textarea>
            <label for="image">Choisir une image :</label>
            <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/gif" required>
            <label for="disponible">Disponible :</label>
            <input type="checkbox" name="disponible" id="disponible" checked>
            <button type="submit" name="add_book">Ajouter le livre</button>
        </form>
    </div>

    <?php $conn->close(); ?>
</body>
</html>