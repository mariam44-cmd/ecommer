<!-- product.php -->

<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$product_id = $_GET['product_id'] ?? null;
if (!$product_id) {
    echo "Produit introuvable.";
    exit();
}

try {
    $connexion = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $requete = $connexion->prepare("SELECT p.*, u.username AS seller_username, u.id AS seller_id FROM products p JOIN users u ON p.seller_id = u.id WHERE p.id = :product_id");
    $requete->bindParam(':product_id', $product_id);
    $requete->execute();
    $produit = $requete->fetch(PDO::FETCH_ASSOC);

    if (!$produit) {
        echo "Produit introuvable.";
        exit();
    }

    $comments_query = $connexion->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.product_id = :product_id ORDER BY c.created_at DESC");
    $comments_query->bindParam(':product_id', $product_id);
    $comments_query->execute();
    $comments = $comments_query->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($produit['name']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container">
    <h1><?php echo htmlspecialchars($produit['name']); ?></h1>
    <p><?php echo htmlspecialchars($produit['description']); ?></p>
    <p>Prix : <?php echo htmlspecialchars($produit['price']); ?> €</p>
    <p>Vendeur : <a href="seller.php?seller_id=<?php echo $produit['seller_id']; ?>"><?php echo htmlspecialchars($produit['seller_username']); ?></a></p>

    <h2>Laisser un commentaire</h2>
    <form action="commenter_produit.php" method="POST">
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        <div class="mb-3">
            <textarea name="comment" class="form-control" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Soumettre</button>
    </form>

    <h2>Commentaires</h2>
    <?php if ($comments): ?>
        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <p><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong> <?php echo htmlspecialchars($comment['comment']); ?></p>
                <p><small><?php echo htmlspecialchars($comment['created_at']); ?></small></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun commentaire pour ce produit.</p>
    <?php endif; ?>
</body>
</html>