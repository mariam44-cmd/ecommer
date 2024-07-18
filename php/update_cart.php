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
    $new_quantity = $_POST['quantity'];

    try {
        $connexion = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
        $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Mettre à jour la quantité de l'article dans le panier
        $updateQuantity = $connexion->prepare("UPDATE cart SET quantity = :quantity WHERE id = :cart_id AND user_id = :user_id");
        $updateQuantity->bindParam(':quantity', $new_quantity);
        $updateQuantity->bindParam(':cart_id', $cart_id);
        $updateQuantity->bindParam(':user_id', $user_id);
        $updateQuantity->execute();

        header('Location: mon_panier.php');
        exit();

    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>