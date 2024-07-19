<?php
session_start();
include '../config.php';

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $entity_id = $_POST['entity_id'];
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$entity_id]);
    header('Location: admin.php');
    exit();
}
?>