<?php
include '../db.php';
session_start();

// Décommenter les lignes suivantes si vous devez restreindre l'accès uniquement aux utilisateurs administrateurs
//  if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
//   header('Location: login.php');
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
            users.username, 
            users.email, 
            users.numero, 
            products.name, 
            orders.quantity, 
            products.price
        FROM orders
        JOIN users ON orders.user_id = users.id
        JOIN products ON orders.product_id = products.id
        ORDER BY orders.order_date DESC
    ");
    $requete->execute();
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
                    <th>Statut</th>
                    <th>Utilisateur</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Prix Unitaire</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $current_order_id = null; ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <?php if ($current_order_id !== $order['order_id']): ?>
                            <?php $current_order_id = $order['order_id']; ?>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo htmlspecialchars($order['email']); ?></td>
                            <td><?php echo htmlspecialchars($order['numero']); ?></td>
                        <?php else: ?>
                            <td colspan="6"></td>
                        <?php endif; ?>
                        <td><?php echo htmlspecialchars($order['name']); ?></td>
                        <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($order['price']); ?> Cfa</td>
                        <td><?php echo htmlspecialchars($order['quantity'] * $order['price']); ?> Cfa</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucune commande trouvée.</p>
    <?php endif; ?>
    <a href="admin.php" class="btn btn-info">Retour au tableau de bord</a>
</div>
</body>
</html>