<?php
session_start();
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
?>

<nav class="navbar">
    <div class="navbar-brand">
        <a href="../index.php">Librairie en Ligne</a>
    </div>
    <ul class="navbar-links">
        <li><a href="../Librairie/Index.php">Accueil</a></li>
        <li><a href="../Librairie/marvel.php">Marvel</a></li>
        <li><a href="../Librairie/dc.php">DC Comics</a></li>
        <li><a href="../Librairie/invincible.php">Invincible</a></li>
        <?php if ($role === 'administrateur') : ?>
            <li><a href="../Librairie/admin.php">Admin Panel</a></li>
        <?php elseif ($role === 'lecteur') : ?>
            <li><a href="../Librairie/mes_emprunts.php">Mes Emprunts</a></li>
        <?php endif; ?>
        <?php if (!$role) : ?>
            <li><a href="../Librairie/login.php">Connexion</a></li>
            <li><a href="../Librairie/register.php">Inscription</a></li>
        <?php else : ?>
            <li><a href="../Librairie/logout.php">Déconnexion</a></li>
        <?php endif; ?>
    </ul>
</nav>