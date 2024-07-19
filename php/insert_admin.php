<?php
include '../config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../db.php';

// Fonction pour hacher le mot de passe
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Informations de l'administrateur
$adminName = 'Admin Name';
$adminEmail = 'mariamdjiree@gmail.com';
$adminPassword = '014810'; // Assurez-vous de le hacher avant de l'insérer
$adminRole = 'admin';

try {
    // Connexion à la base de données
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérification si l'email existe déjà
    $query = $db->prepare("SELECT * FROM users WHERE email = :email");
    $query->bindParam(':email', $adminEmail);
    $query->execute();
    $existingUser = $query->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        echo "Cet email est déjà utilisé.";
    } else {
        // Hachage du mot de passe
        $hashedPassword = hashPassword($adminPassword);

        // Préparation de la requête d'insertion
        $query = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
        $query->bindParam(':name', $adminName);
        $query->bindParam(':email', $adminEmail);
        $query->bindParam(':password', $hashedPassword);
        $query->bindParam(':role', $adminRole);

        // Exécution de la requête
        $query->execute();

        echo "Administrateur ajouté avec succès.";
    }
} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
}
?>