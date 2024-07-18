<?php
session_start();
include 'config.php'; // Fichier de configuration pour les paramètres de la base de données
include 'db.php'; // Fichier pour initialiser la connexion PDO

// Vérification de la méthode de requête et de la soumission du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    // Récupération des données du formulaire
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : '';

    // Validation des champs obligatoires
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        echo "Tous les champs sont obligatoires.";
        exit();
    }

    // Hashage du mot de passe
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Connexion à la base de données
        $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Préparation de la requête SQL pour insérer un utilisateur
        $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $passwordHash, $role]);

        // Redirection après l'ajout réussi
        header('Location: admin.php');
        exit();
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Utilisateur</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <h2>Ajouter un Utilisateur</h2>
    <form action="ajouter_utilisateur.php" method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Nom d'utilisateur</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Rôle</label>
            <select name="role" id="role" class="form-control" required>
                <option value="acheteur">Acheteur</option>
                <option value="vendeur">Vendeur</option>
            </select>
        </div>
        <button type="submit" name="add_user" class="btn btn-primary">Ajouter</button>
    </form>
</body>
</html>
