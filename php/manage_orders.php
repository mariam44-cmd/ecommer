<?php
include '../db.php';
session_start();

// Vérifiez si l'utilisateur est connecté et est un acheteur
// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'buyer') {
//     header('Location: login.php');
//     exit();
// }

try {
    $connexion = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $requete = $connexion->prepare("
        SELECT 
            orders.id as order_id, 
            orders.order_date, 
            orders.status, 
            products.name as product_name, 
            orders.quantity, 
            products.price,
            users.username as seller_name,
            users.email as seller_email,
            users.numero as seller_numero
        FROM orders
        JOIN products ON orders.product_id = products.id
        JOIN users ON products.seller_id = users.id
        WHERE orders.user_id = :user_id
        ORDER BY orders.order_date DESC
    ");
  
    $requete->execute(['user_id' => $_SESSION['user']['id']]);
    $orders = $requete->fetchAll();

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Commandes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
<a href="afficher_enregistrements.php"class="btn btn-info my-2"><h4><</h4></a>
    <h1>Mes Commandes</h1>
    <?php if ($orders): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Commande ID</th>
                    <th>Date de la commande</th>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Prix Unitaire</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Vendeur</th>
                    <th>Email du Vendeur</th>
                    <th>Téléphone du Vendeur</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                        <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($order['price']); ?> Cfa</td>
                        <td><?php echo htmlspecialchars($order['quantity'] * $order['price']); ?> Cfa</td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                        <td><?php echo htmlspecialchars($order['seller_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['seller_email']); ?></td>
                        <td><?php echo htmlspecialchars($order['seller_numero']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucune commande trouvée.</p>
    <?php endif; ?>
    <a href="afficher_enregistrements.php" class="btn btn-primary">Retour</a>
</div>
</body>
</html>