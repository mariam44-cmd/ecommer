<?php
session_start();

include 'config.php';
include 'db.php';

// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
$user = $_SESSION['user'];
$user_id = $_SESSION['user']['id'];
$user_role = $_SESSION['user']['role'];

try {
    // Connexion à la base de données
    $connexion = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les catégories
    $requeteCategories = $connexion->prepare("SELECT * FROM categories");
    $requeteCategories->execute();
    $categories = $requeteCategories->fetchAll();

    // Construire la requête pour récupérer les produits
    $sql = "SELECT * FROM products WHERE quantity > 0";
    $params = [];

    if ($user_role == 'seller') {
        $sql .= " AND seller_id = :seller_id";
        $params[':seller_id'] = $user_id;
    } elseif ($user_role == 'admin' || $user_role == 'buyer') {
        if (isset($_GET['category_id']) && $_GET['category_id'] != '') {
            $sql .= " AND category_id = :category_id";
            $params[':category_id'] = $_GET['category_id'];
        }
    } else {
        die("Rôle d'utilisateur non valide.");
    }

    // Préparer et exécuter la requête des produits
    $requeteProduits = $connexion->prepare($sql);
    $requeteProduits->execute($params);
    $products = $requeteProduits->fetchAll(PDO::FETCH_ASSOC);

    // Si l'utilisateur est un acheteur, récupérer les produits dans le panier
    if ($user_role == 'buyer') {
        $requetePanier = $connexion->prepare("
            SELECT p.*, c.id as cart_items_id
            FROM cart_items c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = :user_id
        ");
        $requetePanier->execute([':user_id' => $user_id]);
        $cart_items = $requetePanier->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $erreur) {
    die("La connexion à la base de données a échoué : " . $erreur->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Afficher les produits disponibles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.4.1/css/glide.core.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.4.1/css/glide.theme.css">
   <link rel="stylesheet" href="../css/styles.css">
</head>
<style>
    .image-container {
            position: relative;
            overflow: hidden;
            object-fit: cover;
        }
        .image-container img {
            width: 100%;
            height: auto;
            animation: zoomInOut 10s infinite;
            object-fit: cover;
            
        }
        @keyframes zoomInOut {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
</style>
<body>
<div class="">
    <?php if ($user_role == 'seller'): ?>
        <a href="ordersV.php" class="btn btn-info my-2">Voir les commandes</a> 
    <?php endif; ?>
    <?php if ($user_role == 'buyer'): ?>
        <header class="header" id="header">
            <div class="navigation">
                
                <div class="">
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                    <a href="manage_orders.php" class="btn btn-outline-dark my-2">Tes commandes</a>
                        <a href="/" class="logo">
                            <img src="../image/logo.png" alt="" width="100px">
                        </a>
                        <form action="search_products.php" method="GET" id="search-form" class="d-flex flex-grow-1 mx-3">
                            <input type="text" class="form-control form-control-lg flex-grow-1" id="search-input" name="search" placeholder="Rechercher des produits">
                            <select class="form-select ms-2" id="category_id" name="category_id" style="width: auto;">
                                <option value="">Catégories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['id']); ?>"
                                        <?php if (isset($_GET['category_id']) && $_GET['category_id'] == $category['id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-light ms-2" name="filter">Filtrer</button>
                        </form>
                        <div class="icons d-flex">
                            <a href="login.php" class="icon me-3">
                                <i class="bx bx-user"></i>
                            </a>
                            <?php if ($user_role == 'buyer'): ?>
                                <a href="panier.php" class="icon me-3 cart-icon position-relative">
                                    <i class="bx bx-cart"></i>
                                    <span class="badge bg-danger"></span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <hr class="my-1">
                    <div class="d-flex justify-content-start flex-wrap">
                        <ul class="nav-list d-flex flex-wrap">
                            <li class="nav-item me-5 mb-2">
                                <a href="afficher_enregistrements.php" class="nav-link">Accueil</a>
                            </li>
                           
                            <li class="nav-item me-5 mb-2">
                                <a href="terms_php" class="nav-link">Conditions</a>
                            </li>
                            <li class="nav-item me-5 mb-2">
                                <a href="propos_du_site.php" class="nav-link">À propos</a>
                            </li>
                            <li class="nav-item me-5 mb-2">
                                <a href="vous_pouvez_contactez.php" class="nav-link">Contact</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class=" image-container r">
              
           
            <img src="..\image\255-800x450.jpeg" alt="" width=""> 
            </div>
        </header>
    <?php endif; ?>
    <?php if ($user_role == 'buyer'): ?>
    <div class="mil text-center">
    <p>&copy; 2024 Ours sanifere. Tout pour revendre et racheter.</p>
    </div>
    <?php endif; ?>
    <div class="container">
        <div class="row mt-4" id="products-container"></div>
        <div class="row mt-4">
            <?php if (empty($products)): ?>
                <p>Aucun produit trouvé.</p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <img src="uploaded_images/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <ul class="card-icons">
                                <li class="icon">
                                    <?php if ($user_role == 'buyer'): ?>
                                        <form action="add_to_cart.php" method="post">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn btn-dark"><i class="bx bx-cart"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </li>
                            </ul>
                            <div class="text-center card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                                <p class="card-text">Prix : <?php echo htmlspecialchars($product['price']); ?> cfa</p>
                                <?php if ($user_role == 'buyer'): ?>
                                    <a href="seller.php?seller_id=<?php echo htmlspecialchars($product['seller_id']); ?>" class="btn btn-outline-primary">Voir le produit</a>
                                <?php endif; ?>
                                <?php if (($user_role == 'seller' && $product['seller_id'] == $_SESSION['user']['id']) || $user_role == 'admin'): ?>
                                    <form action="modifier_produit.php" method="get" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="btn btn-warning">Modifier</button>
                                    </form>
                                    <form action="supprimer_produit.php" method="post" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Supprimer</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php if ($user_role == 'buyer'): ?>
 
   
<!-- Contact -->
<section class="section contact">
    <div class="row">
        <div class="col">
            <h2>EXCELLENT SUPPORT</h2>
            <p>We love our customers and they can reach us any time of day. We will be at your service 24/7.</p>
            <a href="" class="btn btn-1">Contact</a>
        </div>
        <div class="col">
            <form action="commenter_produit.php" method="post">
                <div class="newLetter">
                    <input type="hidden" name="product_id" value="1">
                    <textarea name="comment" placeholder="laissez-nous un commentaire"></textarea>
                    <button type="submit" name="comment" value="Commenter"> envoyer</button>
                </div>
            </form>
        </div>
    </div>
</section>
<!-- Footer -->
<footer class="footer">
    <div class="row">
        <div class="col d-flex">
            <h4>INFORMATIONS</h4>
            <a href="propos_du_site.php">A propos de nous</a>
            <a href="vous_pouvez_contactez.php">Contact nous</a>
            <a href="terms_php">Term & Conditions</a>
            <a href="">Shopping Guide</a>
        </div>
        <div class="col d-flex">
            <h4>Utilite</h4>
            <a href="">Online Store</a>
        </div>
        <div class="col d-flex">
            <span><i class='bx bxl-facebook-square'></i></span>
            <span><i class='bx bxl-instagram-alt' ></i></span>
            <span><i class='bx bxl-github' ></i></span>
            <span><i class='bx bxl-twitter' ></i></span>
            <span><i class='bx bxl-pinterest' ></i></span>
        </div>
    </div>
</footer>
<?php endif; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.4.1/glide.min.js"></script>
<script src="../js/slider.js"></script>
<script src="../js/index.js"></script>

</body>
</html>