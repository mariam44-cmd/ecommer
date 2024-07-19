<?php
session_start();
include '../db.php';
// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$dsn = "mysql:host=localhost;dbname=ecommerce;charset=utf8mb4";
$utilisateur = "root";
$mot_de_passe = "";

try {
    $connexion = new PDO($dsn, $utilisateur, $mot_de_passe);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération de l'ID et du rôle de l'utilisateur actuellement connecté
    $user_id = $_SESSION['user']['id'];
    $user_role = $_SESSION['user']['role'];

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
        $product_id = $_POST['id'];

        // Vérification que l'utilisateur est admin ou que le produit lui appartient
        $requete = $connexion->prepare("SELECT seller_id FROM products WHERE id = ?");
        $requete->execute([$product_id]);
        $product = $requete->fetch();

        if ($product) {
            if ($user_role == 'admin' || $product['seller_id'] == $user_id) {
                // Suppression du produit
                $requete = $connexion->prepare("DELETE FROM products WHERE id = ?");
                $requete->execute([$product_id]);

                if ($user_role == 'admin') {
                    header('Location: admin.php'); // Redirige vers la page d'administration après suppression
                } else {
                    header('Location: afficher_enregistrements.php?seller_id=' . $user_id); // Redirige vers la page du vendeur après suppression
                }
                exit();
            } else {
                die("Vous n'êtes pas autorisé à supprimer ce produit.");
            }
        } else {
            die("Produit non trouvé.");
        }
    } else {
        die("Requête invalide.");
    }
} catch (PDOException $erreur) {
    die("La connexion à la base de données a échoué : " . $erreur->getMessage());
}
?>