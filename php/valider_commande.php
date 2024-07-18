<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user']['id'];

try {
    $connexion = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Début de la transaction
    $connexion->beginTransaction();

    // Récupérer les articles du panier
    $requetePanier = $connexion->prepare("SELECT * FROM cart_items WHERE user_id = :user_id");
    $requetePanier->bindParam(':user_id', $user_id);
    $requetePanier->execute();
    $cart_items = $requetePanier->fetchAll();
    var_dump($cart_items);
    echo('ok');

    if (empty($cart_items)) {
        throw new Exception("Le panier est vide");
    }

    // Créer des commandes avec les articles du panier
    foreach ($cart_items as $item) {
        $requeteCommande = $connexion->prepare("INSERT INTO orders (user_id, product_id, quantity, order_date) VALUES (:user_id, :product_id, :quantity, NOW())");
        $requeteCommande->bindParam(':user_id', $user_id);
        $requeteCommande->bindParam(':product_id', $item['product_id']);
        $requeteCommande->bindParam(':quantity', $item['quantity']);
        $requeteCommande->execute();
        echo "Commande insérée pour le produit_id {$item['product_id']} avec quantité {$item['quantity']}<br>";
    }

    // Vider le panier de l'utilisateur
    $requeteViderPanier = $connexion->prepare("DELETE FROM cart_items WHERE user_id = :user_id");
    $requeteViderPanier->bindParam(':user_id', $user_id);
    $requeteViderPanier->execute();

    // Commit de la transaction
    $connexion->commit();

    header('Location: commande_confirmee.php');
    exit();

} catch (PDOException $e) {
    // Rollback de la transaction en cas d'erreur
    $connexion->rollBack();
    die("Erreur PDO : " . $e->getMessage());
} catch (Exception $e) {
    // Rollback de la transaction en cas d'erreur
    $connexion->rollBack();
    die("Erreur : " . $e->getMessage());
}
?>