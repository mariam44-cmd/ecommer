<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cart_id = $_POST['cart_id'];

    try {
        $connexion = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
        $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Supprimer l'article du panier
        $deleteItem = $connexion->prepare("DELETE FROM cart_items WHERE id = :cart_id AND user_id = :user_id");
        $deleteItem->bindParam(':cart_id', $cart_id);
        $deleteItem->bindParam(':user_id', $user_id);
        $deleteItem->execute();

        header('Location: panier.php');
        exit();

    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>