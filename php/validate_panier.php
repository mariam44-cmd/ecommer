<?php
include '../db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user']['id'];

try {
    $connexion = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Créer une nouvelle commande avec la date actuelle
    $insertCommande = $connexion->prepare("INSERT INTO orders (user_id, order_date) VALUES (:user_id, NOW())");
    $insertCommande->bindParam(':user_id', $user_id);
    $insertCommande->execute();
    $order_id = $connexion->lastInsertId();

    if (!$order_id) {
        throw new Exception("Échec de l'insertion de la commande");
    }

    // Récupérer les articles du panier
    $requete = $connexion->prepare("SELECT * FROM cart_items WHERE user_id = :user_id");
    $requete->bindParam(':user_id', $user_id);
    $requete->execute();
    $cart_items = $requete->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cart_items)) {
        throw new Exception("Le panier est vide");
    }

    // Ajouter les articles à la commande
    foreach ($cart_items as $item) {
        $insertOrder = $connexion->prepare("INSERT INTO orders (user_id, product_id, quantity, order_date) VALUES (:user_id, :product_id, :quantity, :order_date)");
        $insertOrder->bindParam(':user_id', $user_id);
        $insertOrder->bindParam(':product_id', $item['product_id']);
        $insertOrder->bindParam(':quantity', $item['quantity']);
        $insertOrder->bindParam(':order_date', date('Y-m-d H:i:s')); // Insérer la date actuelle ici

        $insertOrder->execute();
    }

    // Vider le panier de l'utilisateur
    $deleteCartItems = $connexion->prepare("DELETE FROM cart_items WHERE user_id = :user_id");
    $deleteCartItems->bindParam(':user_id', $user_id);
    $deleteCartItems->execute();

    header('Location: commande_confirmee.php');
    exit();

} catch (PDOException $e) {
    die("Erreur PDO : " . $e->getMessage());
} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}
?>