<?php
session_start();
include '../config.php';
include '../db.php';

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'] ?? '';
    $category_description = $_POST['category_description'] ?? '';

    $stmt = $db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt->execute([$category_name, $category_description]);
    header('Location: admin.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Catégorie</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<h2>Ajouter une Catégorie</h2>
<form action="ajouter_categorie.php" method="POST">
    <div class="mb-3">
        <label for="category_name" class="form-label">Nom de la catégorie</label>
        <input type="text" name="category_name" id="category_name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="category_description" class="form-label">Description de la catégorie</label>
        <input type="text" name="category_description" id="category_description" class="form-control" required>
    </div>
    <button type="submit" name="add_category" class="btn btn-primary">Ajouter</button>
</form>
</body>
</html>