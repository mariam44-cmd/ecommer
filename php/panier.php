<?php
session_start();
include 'db.php';

// Vérification de l'authentification de l'utilisateur
// if (!isset($_SESSION['user'])) {
//     header('Location: login.php');
//     exit();
// }

$user_id = $_SESSION['user']['id'];

try {
    // Connexion à la base de données
    $connexion = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Supprimer un article du panier
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
        $product_id = $_POST['product_id'];
        $requete = $connexion->prepare("DELETE FROM cart_items WHERE user_id = :user_id AND product_id = :product_id");
        $requete->bindParam(':user_id', $user_id);
        $requete->bindParam(':product_id', $product_id);
        $requete->execute();
    }

    // Récupérer les produits dans le panier de l'utilisateur actuel
    $requete = $connexion->prepare("
        SELECT products.*, cart_items.quantity
        FROM cart_items
        JOIN products ON cart_items.product_id = products.id
        WHERE cart_items.user_id = :user_id
    ");
    $requete->bindParam(':user_id', $user_id);
    $requete->execute();
    $cart_items = $requete->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.4.1/css/glide.core.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.4.1/css/glide.theme.css">
    <link rel="stylesheet" href="../css/styles.css">

    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            background-image: url('../image/panier.jpg');
            background-size: cover;
            background-attachment: fixed;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            padding: 20px;
            width: 80%;
            max-width: 800px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-animate {
            animation: fadeIn 1s ease-in-out;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        
        <h1 class="mb-4">Mon Panier</h1>
        <?php if ($cart_items): ?>
            <table class="table table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Image</th>
                        <th>Produit</th>
                        <th>vendeur</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total = 0; ?>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><img src="uploaded_images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-fluid" style="width: 50px; height: auto;"></td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['seller_id']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($item['price']); ?> Cfa</td>
                            <td><?php echo htmlspecialchars($item['price'] * $item['quantity']); ?> Cfa</td>
                            <td>
                                <form action="panier.php" method="post">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                </form>
                                <a href="seller.php?seller_id=<?php echo htmlspecialchars($item['seller_id']); ?>" class="btn btn-outline-primary">Voir plut</a>
                   
                            </td>
                        </tr>
                        <?php $total += $item['price'] * $item['quantity']; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="d-flex justify-content-end mb-3">
                <h3>Total: <?php echo $total; ?> €</h3>
            </div>
            <div class="d-flex justify-content-between">
                <a href="afficher_enregistrements.php" class="btn btn-primary btn-animate">Continuer les achats</a>
                <form action="validate_panier.php" method="POST">
                    <button type="submit" class="btn btn-success btn-animate">Valider panier</button>
                </form>
            </div>
        <?php else: ?>
            <p>Votre panier est vide.</p>
            <a href="afficher_enregistrements.php" class="btn btn-primary btn-animate">Continuer les achats</a>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>