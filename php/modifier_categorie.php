<?php
session_start();
include 'config.php';
include 'db.php';

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit();
}

$entity_id = $_POST['entity_id'] ?? null;
if ($entity_id) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$entity_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_category'])) {
    $category_name = $_POST['category_name'] ?? '';
    $category_description = $_POST['category_description'] ?? '';
    $stmt = $db->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
    $stmt->execute([$category_name, $category_description, $entity_id]);
    header('Location: admin.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Catégorie</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<h2>Modifier Catégorie</h2>
<form action="modifier_categorie.php" method="POST">
    <input type="hidden" name="entity_id" value="<?php echo $entity_id; ?>">
    <div class="mb-3">
        <label for="category_name" class="form-label">Nom de la catégorie</label>
        <input type="text" name="category_name" id="category_name" class="form-control" value="<?php echo $category['name']; ?>" required>
    </div>
   
    <button type="submit" name="update_category" class="btn btn-primary">Modifier</button>
</form>
</body>
</html>