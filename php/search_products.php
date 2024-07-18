<?php
include 'db.php';

// Check if the search parameter is set
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = htmlspecialchars($_GET['search']);
    // Prepare the search query
    $query = $connexion->prepare("
        SELECT products.*, categories.name AS category_name 
        FROM products 
        JOIN categories ON products.category_id = categories.Id 
        WHERE (products.name LIKE :search OR products.seller_id LIKE :search)
        AND products.quantity > 0
        ORDER BY RAND()
    ");
    $query->execute(['search' => "%$search%"]);
    $products = $query->fetchAll(PDO::FETCH_ASSOC);
} else {
    // If no search term is provided, redirect to the homepage or show an error
    header('Location: afficher_enregistrements.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche</title>
    <link rel="stylesheet" href="Style/list_livre.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .book-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .book {
            border: 1px solid #ddd;
            padding: 15px;
            width: calc(25% - 20px);
            box-sizing: border-box;
            text-align: center;
        }
        .book img {
            max-width: 500px;
            max-height: 400px;
            margin-bottom: 10px;
        }
        .book h3 {
            font-size: 1.2em;
            margin: 10px 0;
        }
        .book p {
            margin: 5px 0;
        }
    </style>
</head>
<body>

<main class="container mt-5">
    <h2>Résultats de recherche</h2>
    <div class="book-grid">
        <?php if (count($products) > 0): ?>
            <?php foreach($products as $product): ?>
                <div class="book">
                    <a href="details.php?Id=<?= htmlspecialchars($product['id']) ?>">
                        <?php if (isset($product['image']) && !empty($product['image'])): ?>
                            <img src="uploaded_images/<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php else: ?>
                            <p>Image non disponible</p>
                        <?php endif; ?>
                    </a>
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p><?= htmlspecialchars($product['description']) ?></p>
                    <p>Prix: <?= htmlspecialchars($product['price']) ?> fcfa</p>
                    <form action="add_to_cart.php" method="post">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-primary">Ajouter au Panier</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun produit trouvé.</p>
        <?php endif; ?>
    </div>
</main>

</body>
</html>