<?php
include 'db.php';
session_start();

// Vérifiez si l'utilisateur est connecté et est un vendeur
// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'seller') {
//     header('Location: login.php');
//     exit();
// }

try {
    $connexion = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $requete = $connexion->prepare("
        SELECT orders.id as order_id, orders.order_date, orders.status, buyer.username as buyer_name, buyer.email as buyer_email, buyer.numero as buyer_numero, products.name, orders.quantity, products.price
        FROM orders
        JOIN users AS buyer ON orders.user_id = buyer.id
        JOIN products ON orders.product_id = products.id
        WHERE products.seller_id = :seller_id
        ORDER BY orders.order_date DESC
    ");
    $requete->execute(['seller_id' => $_SESSION['user']['id']]);
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
    <title>Gestion des Commandes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Gestion des Commandes</h1>
    <?php if ($orders): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Commande ID</th>
                    <th>Date de la commande</th>
                    <th>Utilisateur</th>
                    <th>Email Utilisateur</th>
                    <th>Téléphone Utilisateur</th>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Prix Unitaire</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                        <td><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['buyer_email']); ?></td>
                        <td><?php echo htmlspecialchars($order['buyer_numero']); ?></td>
                        <td><?php echo htmlspecialchars($order['name']); ?></td>
                        <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($order['price']); ?> Cfa</td>
                        <td><?php echo htmlspecialchars($order['quantity'] * $order['price']); ?> €</td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                        <td>
                            <?php if ($order['status'] == 'pending'): ?>
                                <form action="validate_order.php" method="post" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <button type="submit" class="btn btn-success">Valider</button>
                                </form>
                            <?php else: ?>
                                <span class="badge bg-success">Validée</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucune commande trouvée.</p>
    <?php endif; ?>
    <a href="seller_page.php" class="btn btn-primary">Retour au tableau de bord</a>
</div>
</body>
</html>