<?php
session_start();

// Vérification de l'authentification de l'utilisateur
// if (!isset($_SESSION['user'])) {
//     // Redirection vers la page de connexion si l'utilisateur n'est pas authentifié
//     header('Location: login.php');
//     exit();
// }

// Initialisation des variables
$seller = null;
$products = [];

// Récupérer le seller_id depuis l'URL
if (isset($_GET['seller_id'])) {
    $seller_id = $_GET['seller_id'];

    // Connexion à la base de données (à remplacer par votre propre configuration)
    // $host = 'localhost';
    // $dbname = 'ecommerce';
    // $username = 'root';
    // $password = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer les informations du vendeur avec cet ID depuis la table users
        $stmt_seller = $pdo->prepare('SELECT * FROM users WHERE id = :seller_id AND role = "seller"');
        $stmt_seller->execute(['seller_id' => $seller_id]);
        $seller = $stmt_seller->fetch(PDO::FETCH_ASSOC);

        if (!$seller) {
            die("Vendeur non trouvé.");
        }

        // Récupérer les produits publiés par ce vendeur
        $stmt_products = $pdo->prepare('SELECT * FROM products WHERE seller_id = :seller_id');
        $stmt_products->execute(['seller_id' => $seller_id]);
        $products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }

} else {
    // Gérer le cas où seller_id n'est pas spécifié dans l'URL
    echo "ID du vendeur non spécifié.";
    // Ou rediriger vers une page d'erreur, etc.
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Vendeur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.4.1/css/glide.core.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.4.1/css/glide.theme.css">
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body class="container">
    <header>
        <!-- Votre en-tête de navigation -->
    </header>

    <?php if ($seller): ?>
        <div class="text-center mt-4">
            <h1>Bienvenue sur la page du vendeur <?php echo htmlspecialchars($seller['username']); ?></h1>
            <p>Vous pouvez entrer en contact avec le vendeur :</p>
            <ul>
                <li>Nom d'utilisateur : <?php echo htmlspecialchars($seller['username']); ?></li>
                <li>Email : <?php echo htmlspecialchars($seller['email']); ?></li>
                <li>Numéro de téléphone : <?php echo htmlspecialchars($seller['numero']); ?></li>
            </ul>

            <!-- Formulaire de contact -->
            
        </div>

        <div class="text-center mt-4">
            <h2>Produits publiés par ce vendeur :</h2>
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <img src="uploaded_images/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="card-body">
                                <h5 class="text-center card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                                <p class="card-text">Prix : <?php echo htmlspecialchars($product['price']); ?> cfa</p>
                                <!-- Ajoutez d'autres informations sur le produit si nécessaire -->
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger" role="alert">
            Vendeur non trouvé ou ID du vendeur non spécifié.
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>