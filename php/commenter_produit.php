<?php
session_start();

// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vous devez être connecté pour laisser un commentaire']);
    exit();
}

// Vérification de la méthode de requête et des données nécessaires
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id']) && isset($_POST['comment'])) {
    $user_id = $_SESSION['user']['id'];
    $product_id = $_POST['product_id'];
    $comment = $_POST['comment'];

    try {
        // Connexion à la base de données
        $connexion = new PDO("mysql:host=localhost;dbname=ecommerce;charset=utf8mb4", "root", "");
        $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Préparation de la requête d'insertion du commentaire
        $insert_comment = $connexion->prepare("INSERT INTO comments (user_id, product_id, comment) VALUES (:user_id, :product_id, :comment)");
        $insert_comment->bindParam(':user_id', $user_id);
        $insert_comment->bindParam(':product_id', $product_id);
        $insert_comment->bindParam(':comment', $comment);
        $insert_comment->execute();

        header('Location: afficher_enregistrements.php');
        exit();

        
    } catch (PDOException $e) {
        // Erreur PDO : retourner un message JSON avec l'erreur
        echo json_encode(['status' => 'error', 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
    } 
} else {
    // Requête invalide : retourner un message JSON avec l'erreur
    echo json_encode(['status' => 'error', 'message' => 'Requête invalide']);
}
?>
