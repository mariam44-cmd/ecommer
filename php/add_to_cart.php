<?php
include '../db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user']['id'];

// Vérification si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
        $product_id = intval($_POST['product_id']); // Récupération de l'identifiant du produit depuis les données POST
        $quantity = intval($_POST['quantity']); // Récupération de la quantité depuis les données POST

        if ($quantity <= 0) {
            die("La quantité doit être supérieure à zéro.");
        }

        try {
            $connexion = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_password);
            $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Vérifier la quantité disponible pour le produit
            $requete = $connexion->prepare("SELECT quantity, seller_id FROM products WHERE id = :product_id");
            $requete->bindParam(':product_id', $product_id); // Liaison du paramètre product_id
            $requete->execute();
            $produit = $requete->fetch(PDO::FETCH_ASSOC); // Récupération des résultats de la requête

            // Vérification si la quantité demandée est disponible
            if ($produit && $produit['quantity'] >= $quantity) {
                $seller_id = $produit['seller_id'];

                // Vérifier si le produit est déjà dans le panier
                $requete = $connexion->prepare("SELECT * FROM cart_items WHERE user_id = :user_id AND product_id = :product_id");
                $requete->bindParam(':user_id', $user_id); // Liaison du paramètre user_id
                $requete->bindParam(':product_id', $product_id); // Liaison du paramètre product_id
                $requete->execute();
                $item = $requete->fetch(PDO::FETCH_ASSOC); // Récupération des résultats de la requête

                if ($item) {
                    // Mettre à jour la quantité si le produit est déjà dans le panier
                    $nouvelle_quantite = $item['quantity'] + $quantity;
                    $updateRequete = $connexion->prepare("UPDATE cart_items SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id");
                    $updateRequete->bindParam(':quantity', $nouvelle_quantite); // Liaison du paramètre nouvelle_quantite
                    $updateRequete->bindParam(':user_id', $user_id); // Liaison du paramètre user_id
                    $updateRequete->bindParam(':product_id', $product_id); // Liaison du paramètre product_id
                    $updateRequete->execute();
                } else {
                    // Ajouter un nouvel article au panier
                    $insertRequete = $connexion->prepare("INSERT INTO cart_items (user_id, product_id, seller_id, quantity) VALUES (:user_id, :product_id, :seller_id, :quantity)");
                    $insertRequete->bindParam(':user_id', $user_id); // Liaison du paramètre user_id
                    $insertRequete->bindParam(':product_id', $product_id); // Liaison du paramètre product_id
                    $insertRequete->bindParam(':seller_id', $seller_id); // Liaison du paramètre seller_id
                    $insertRequete->bindParam(':quantity', $quantity); // Liaison du paramètre quantity
                    $insertRequete->execute();
                }

                // Décrémenter la quantité du produit dans la base de données
                $nouvelle_quantite_produit = $produit['quantity'] - $quantity;
                $updateProduitRequete = $connexion->prepare("UPDATE products SET quantity = :quantity WHERE id = :product_id");
                $updateProduitRequete->bindParam(':quantity', $nouvelle_quantite_produit); // Liaison du paramètre nouvelle_quantite_produit
                $updateProduitRequete->bindParam(':product_id', $product_id); // Liaison du paramètre product_id
                $updateProduitRequete->execute();

                // Redirection vers la page du panier après l'ajout du produit
                header('Location: panier.php');
                exit(); // Arrêt du script
            } else {
                // Affichage d'un message si la quantité demandée n'est pas disponible
                echo "Quantité insuffisante pour le produit.";
            }
        } catch (PDOException $e) {
            // Gestion des erreurs de la base de données
            die("Erreur : " . $e->getMessage());
        }
    } else {
        // Gestion des cas où les paramètres POST sont manquants
        die("Paramètres manquants.");
    }
} else {
    // Gestion des requêtes qui ne sont pas de type POST
    echo "Méthode de requête non valide.";
}
?>