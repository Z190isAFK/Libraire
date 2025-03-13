# Projet Librairie en Ligne

Le **Projet Librairie en Ligne** est une application web simple qui permet de gérer une collection de livres numériques (comics) et de suivre les emprunts des utilisateurs. Développé avec **PHP**, **MySQL**, **HTML**, et **CSS**, ce projet est conçu pour être hébergé sur un serveur local (comme XAMPP) ou un serveur distant.

## Description du projet

Ce projet simule une librairie en ligne où :
- Les **utilisateurs (lecteurs)** peuvent s’inscrire, se connecter, consulter des livres organisés par séries (Marvel, DC Comics, Invincible), emprunter des livres disponibles, et voir leur historique d’emprunts.
- Les **administrateurs** peuvent gérer les livres (ajout, suppression) et les emprunts des utilisateurs (rendre un livre, effacer l’historique).

Le site est organisé autour de trois séries de livres (Marvel, DC Comics, Invincible), et les livres sont accompagnés d’images uploadées par l’administrateur.

## Ce qu’on peut faire avec ce projet

- **Pour les lecteurs** :
  - S’inscrire et se connecter à un compte.
  - Parcourir les livres par série (Marvel, DC Comics, Invincible).
  - Voir les détails d’un livre (titre, série, numéro, description, image).
  - Emprunter un livre disponible et consulter ses emprunts en cours ou passés.
  - Se déconnecter.

- **Pour les administrateurs** :
  - Se connecter avec un compte admin (par défaut : `admin` / `admin123`).
  - Voir la liste des utilisateurs et leurs emprunts.
  - Rendre un livre pour le remettre à disposition.
  - Effacer l’historique des emprunts rendus d’un utilisateur.
  - Ajouter un nouveau livre (titre, série, numéro, description, image, disponibilité).
  - Supprimer un livre existant.

## Structure et description des fichiers

Le projet est organisé dans un dossier principal `projet-librairie/`. Voici une description de chaque fichier et dossier :

- **CSS/**
  - `styles.css` : Feuille de style globale qui définit le design du site (couleurs, mise en page, boutons, etc.).

- **images/** : Dossier où sont stockées les images uploadées pour les livres (par exemple, `66f7d12345abc_image.jpg` pour un livre ajouté).

- `admin.php` : Panneau d’administration réservé aux administrateurs. Permet de gérer les emprunts (rendre un livre, effacer l’historique) et les livres (ajout, suppression).

- `dc.php` : Affiche la liste des livres de la série **DC Comics** avec leurs détails (titre, numéro, image, disponibilité).

- `index.php` : Page d’accueil du site. Affiche les trois séries (Marvel, DC Comics, Invincible) avec des liens vers leurs pages respectives.

- `inscription.php` : Page permettant aux utilisateurs de s’inscrire (nom d’utilisateur, email, mot de passe).

- `invincible.php` : Affiche la liste des livres de la série **Invincible** avec leurs détails.

- `livre.php` : Page de détails d’un livre spécifique. Affiche le titre, la série, le numéro, la description, l’image, et un bouton pour emprunter si le livre est disponible.

- `login.php` : Page de connexion pour les utilisateurs et administrateurs (nom d’utilisateur et mot de passe).

- `logout.php` : Script qui déconnecte l’utilisateur et le redirige vers la page de connexion.

- `marvel.php` : Affiche la liste des livres de la série **Marvel** avec leurs détails.

- `mes_emprunts.php` : Page où un utilisateur connecté peut voir ses emprunts en cours et son historique d’emprunts rendus.

- `navbar.php` : Barre de navigation incluse dans toutes les pages. Contient des liens vers les pages principales (accueil, séries, mes emprunts, panneau admin, déconnexion).

- `database.sql` : Fichier SQL pour créer la base de données `librairie`, les tables (`utilisateurs`, `livres`, `prets`), et insérer des données initiales (utilisateurs et livres).

## Comment utiliser le projet

1. **Installer le projet** :
   - Clonez le dépôt GitHub dans le répertoire de votre serveur web (par exemple, `C:\xampp\htdocs\projet-librairie`).
   - Créez une base de données `librairie` dans phpMyAdmin et importez `database.sql`.
   - Assurez-vous que le dossier `images/` a les permissions d’écriture (voir les instructions dans `database.sql`).

2. **Pour les lecteurs** :
   - Inscrivez-vous via `inscription.php` et connectez-vous via `login.php`.
   - Parcourez les livres sur `index.php`, `marvel.php`, `dc.php`, ou `invincible.php`.
   - Consultez les détails d’un livre sur `livre.php` et empruntez-le si disponible.
   - Suivez vos emprunts sur `mes_emprunts.php`.

3. **Pour les administrateurs** :
   - Connectez-vous avec `admin` / `admin123`.
   - Accédez à `admin.php` pour gérer les utilisateurs et les livres.

## Base de données

La base de données (`librairie`) contient trois tables :
- `utilisateurs` : Informations des utilisateurs (id, username, email, password, role).
- `livres` : Informations des livres (id, titre, serie, numero, description, image, disponible).
- `prets` : Informations des emprunts (id, utilisateur_id, livre_id, date_debut, date_fin).

Le fichier `database.sql` fournit tout ce qu’il faut pour configurer la base de données.

## Contributeurs

- [Z190] : Développeur principal.

---

Si vous avez des questions ou souhaitez contribuer, ouvrez une issue sur GitHub !