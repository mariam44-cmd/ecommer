<?php
include 'db.php';
session_start();

// Vérifiez si l'utilisateur est connecté et est un vendeur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'seller') {
    header('Location: login.php');
    exit();
}

// Vérifiez si l'ID de la commande est fourni
if (!isset($_POST['order_id'])) {
    header('Location: manage_orders.php');
    exit();
}

$order_id = $_POST['order_id'];

try {
    $connexion = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Mettre à jour le statut de la commande à "validated"
    $requete = $connexion->prepare("UPDATE orders SET status = 'validated' WHERE id = :order_id");
    $requete->execute(['order_id' => $order_id]);

    // Rediriger vers la page de gestion des commandes
    header('Location: manage_orders.php');
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>