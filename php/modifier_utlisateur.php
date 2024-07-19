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

$entity_id = $_POST['entity_id'] ?? null;
if ($entity_id) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$entity_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
    $stmt->execute([$username, $email, $role, $entity_id]);
    header('Location: admin.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Utilisateur</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<h2>Modifier Utilisateur</h2>
<form action="modifier_utlisateur.php" method="POST">
    <input type="hidden" name="entity_id" value="<?php echo $entity_id; ?>">
    <div class="mb-3">
        <label for="username" class="form-label">Nom d'utilisateur</label>
        <input type="text" name="username" id="username" class="form-control" value="<?php echo $user['username']; ?>" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control" value="<?php echo $user['email']; ?>" required>
    </div>
    <div class="mb-3">
        <label for="role" class="form-label">Rôle</label>
        <select name="role" id="role" class="form-control" required>
            <option value="admin" <?php if($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
            <option value="seller" <?php if($user['role'] == 'seller') echo 'selected'; ?>>Vendeur</option>
        </select>
    </div>
    <button type="submit" name="update_user" class="btn btn-primary">Modifier</button>
</form>
</body>
</html>