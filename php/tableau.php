<?php
session_start();

// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Récupération des informations de l'utilisateur depuis la session
$user = $_SESSION['user'];

// Vérification si l'utilisateur est un vendeur
if ($user['role'] !== 'seller') {
    header('Location: afficher_enregistrements.php');
    exit();
}

// Connexion à la base de données
$host = 'localhost';
$dbname = 'ecommerce';
$username = 'root';
$password = '';

try {
    $connexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérification et traitement des données du formulaire
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        // Affichage des superglobales pour diagnostic
        echo '<pre>';
        print_r($_POST);
        print_r($_FILES);
        echo '</pre>';

        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? '';
        $quantity = $_POST['quantity'] ?? '';
        $category_id = $_POST['category_id'] ?? '';
        $seller_id = $user['id'];

        // Traitement de l'image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = $_FILES['image']['name'];
            $fileSize = $_FILES['image']['size'];
            $fileType = $_FILES['image']['type'];
            var_dump($_FILES['image']); // Affichage des informations sur l'image
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = array('jpg', 'jpeg', 'png');

            // Vérifier si l'extension du fichier est autorisée
            if (in_array($fileExtension, $allowedExtensions)) {
                $uploadDir = 'uploaded_images/';
                
                // Vérifiez si le répertoire de téléchargement existe, sinon créez-le
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $uniqueFileName = uniqid('image_') . '.' . $fileExtension;
                $destination = $uploadDir . $uniqueFileName;

                // Déplacer le fichier téléchargé vers le répertoire de destination
                if (move_uploaded_file($fileTmpPath, $destination)) {
                    // Insertion du produit dans la base de données avec le nom unique du fichier
                    $requete = $connexion->prepare('INSERT INTO products (name, description, price, quantity, image, category_id, seller_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
                    $requete->execute([$name, $description, $price, $quantity, $uniqueFileName, $category_id, $seller_id]);

                    // Affichage du nom de l'image après insertion
                    echo "Image téléchargée avec succès. Nom de l'image: " . $uniqueFileName;

                    // Redirection après l'insertion des données
                    header('Location: afficher_enregistrements.php?id=' . $seller_id);
                    exit();
                } else {
                    echo "Erreur lors du déplacement du fichier téléchargé.";
                }
            } else {
                echo "Type de fichier non autorisé. Les types autorisés sont : jpg, jpeg, png.";
            }
        } else {
            echo "Erreur lors du téléchargement de l'image : " . $_FILES['image']['error'];
        }
    }
} catch (PDOException $e) {
    die("Could not connect to the database $dbname :" . $e->getMessage());
}
?>
















<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';

// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
$search = isset($_GET['search']) ? $_GET['search'] : '';

$user_id = $_SESSION['user']['id'];
$user_role = $_SESSION['user']['role'];

try {
    // Connexion à la base de données
    // $connexion = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    // $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les informations de l'utilisateur, y compris product_id et cart_id
    $requeteUser = $connexion->prepare("SELECT * FROM users WHERE id = :user_id");
    $requeteUser->execute([':user_id' => $user_id]);
    $user_info = $requeteUser->fetch(PDO::FETCH_ASSOC);

    // Récupérer les catégories
    $requeteCategories = $connexion->prepare("SELECT * FROM categories");
    $requeteCategories->execute();
    $categories = $requeteCategories->fetchAll();
    $sql = "SELECT * FROM products WHERE quantity > 0";
    $params = [];
    // Récupérer les produits en fonction du rôle de l'utilisateur
    $sql = "SELECT * FROM products where quantity > 0";
    $params = [];

    if ($user_role == 'seller') {
        // Pour les vendeurs, récupérer les produits créés par le vendeur
        $sql .= " AND seller_id = :seller_id";
        $params = [':seller_id' => $user_id];
    } elseif ($user_role == 'buyer') {
        // Pour les acheteurs, récupérer tous les produits
        // Pas besoin de condition spécifique pour les acheteurs, ils voient tous les produits
        $params = [];
    

        if (isset($_GET['category_id']) && $_GET['category_id'] != '') {
            $sql .= " WHERE category_id = :category_id";
            $params[':category_id'] = $_GET['category_id'];
        }
    } elseif ($user_role == 'admin' || $user_role == 'buyer') {
        if (isset($_GET['category_id']) && $_GET['category_id'] != '') {
            $sql .= " WHERE category_id = :category_id";
            $params[':category_id'] = $_GET['category_id'];
        }
    } else {
        die("Rôle d'utilisateur non valide.");
    }

    $requeteProduits = $connexion->prepare($sql);
    $requeteProduits->execute($params);
    $products = $requeteProduits->fetchAll(PDO::FETCH_ASSOC);

    if ($user_role == 'buyer') {
        // Récupérer les produits dans le panier pour les acheteurs
        $requetePanier = $connexion->prepare("
            SELECT p.*, c.id as cart_items_id
            FROM cart_items c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = :user_id
        ");
        $requetePanier->execute([':user_id' => $user_id]);
        $cart_items = $requetePanier->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Récupérer les commentaires pour chaque produit
    foreach ($products as &$product) {
        $requeteComments = $connexion->prepare("SELECT * FROM comments WHERE product_id = :product_id");
        $requeteComments->bindParam(':product_id', $product['id']);
        $requeteComments->execute();
        $product['comments'] = $requeteComments->fetchAll(PDO::FETCH_ASSOC);
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
<style>
     header {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: Arial, sans-serif;
           
            color: white;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to top, #ffffff 0%, #aaa8a8 50%, #ffffff 100%);
        background-size: 200% 200%;
        animation: animateBody 15s ease infinite;
    
        }
    .card {
        position: relative;
        overflow: hidden;
    }
    .card-img-top {
        transition: transform 0.3s ease;
    }
    .card:hover .card-img-top {
        transform: scale(1.1);
    }

    .card-icons {
        position: absolute;
        top: 80px;
        right: 10px;
        display: flex;
        gap: 10px;
        transform: translateX(-50%);
        opacity: 0;
        transition: opacity 0.3s ease;
        align-items: center;
    }
    .card:hover .card-icons {
        opacity: 1;
    }
   
    .image-container {
       
        position: relative;
            width: 1000px;
            max-width: 800px;
            margin: 20px auto;
           
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
    } 

    .header {
        animation: slideInDown 1s ease-in-out;
    }

    @keyframes slideInDown {
        from {
            transform: translateY(-100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .card {
        animation: fadeIn 1s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    
        /* Animation pour fonts */
        
    
        .shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(-45deg);
            animation: float 10s ease-in-out infinite;
        }

        .shape::before,
        .shape::after {
            content: "";
            position: absolute;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
        }

        .shape::before {
            top: -25px;
            left: 0;
        }

        .shape::after {
            top: 0;
            left: 25px;
        }

        @keyframes float {
            0% {
                transform: translateY(0) translateX(0) rotate(-45deg);
            }
            50% {
                transform: translateY(-50px) translateX(50px) rotate(-45deg);
            }
            100% {
                transform: translateY(0) translateX(0) rotate(-45deg);
            }
        }


        /* Animation pour les icônes */
        .heart-icon:hover {
            animation: heartBeat 1s ease infinite;
        }
        .cart-icon:hover {
            animation: bounce 0.5s ease;
        }
        .search-icon:hover {
            transform: scale(1.1);
        }
        @keyframes heartBeat {
            0% {
                transform: scale(1);
            }
            25% {
                transform: scale(1.1);
            }
            50% {
                transform: scale(1);
            }
            75% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }
        @keyframes bounce {
            0% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
            100% {
                transform: translateY(0);
            }
        }
        /* Transition pour le formulaire de recherche */
        #search-form {
            transition: width 0.3s ease;
        }
        #search-form:hover {
            width: 300px;
        }
        /* Effet de survol pour les liens de navigation */
        .nav-link {
            position: relative;
            transition: color 0.3s ease;
        }
        .nav-link::before {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 0%;
            height: 2px;
            background-color: #ff6699;
            transition: width 0.3s ease;
        }
        .nav-link:hover::before {
            width: 100%;
        }
        /* Effet de badge pour le panier et les favoris */
        .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            font-size: 0.8rem;
            font-weight: bold;
            min-width: 18px;
            height: 18px;
            line-height: 18px;
            padding: 0 4px;
            border-radius: 50%;
        }
        .slides {
            display: flex;
            transition: transform 0.5s ease-in-out;
            width: 100%; /* Ensure slides take full width */
        }
        .slide {
            min-width: 100px;
            max-width: 100%;
            height: auto; /* Ensure aspect ratio is maintained */
            object-fit: cover; /* Crop and fit image to slider */
          
            transition: opacity 0.5s ease-in-out;
        }
        
        .bg-danger {
            background-color: #ff3d71;
            color: #fff;
        }
        .footer{
            display: block !important;
        }
    
</style>
</head>
<div class="hed">
    <?php if ($user_role == 'seller'): ?>
                            <a href="ordersV.php" class="btn btn-info my-2">Voir les commandes</a> 
                            <!-- <a href="afficher_commandes?buyer_id=<?php echo htmlspecialchars($orders['buyer_id']); ?>" class="btn btn-outline-primary">commande fait </a> --> 
                              <?php endif; ?>
<?php if ($user_role == 'buyer'): ?>
    <header class="header" id="header">
        <div class="navigation">
            <div class="container">
                <!-- Logo, Search Bar, and Icons on the Same Line -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <a href="/" class="logo">
                        <img src="../image/logo.png" alt="" width="100px">
                    </a>

                    <div class="search-bar">
            <form action="search_products.php" method="GET">
                <input type="text" name="search" placeholder="Rechercher un livre...">
                <button type="submit">Rechercher</button>
            </form>
                    <!-- <form  action="search_products.php" method="POST" id="search-form" class="d-flex flex-grow-1 mx-3" >
                        <input type="text" class="form-control-lg flex-grow-1" id="search-input" name="search" placeholder="Rechercher des produits" height="200px">
<!--                          -->
                        <select class="form-select me-2" id="category_id" name="category_id" style="width: auto;">
                            <option value="">Catégories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<§--?php echo htmlspecialchars($category['id']); ?>"
                                    <?php if (isset($_GET['category_id']) && $_GET['category_id'] == $category['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select> 
                        <input type="submit" class="btn btn-light" name="filter" >Filtrer</input>
                    </form>
                    <div class="icons d-flex">
                        <a href="login.php" class="icon me-3">
                            <i class="bx bx-user"></i>
                        </a>
                        <div class="icon me-3 search-icon">
                            <i class="bx bx-search"></i>
                        </div>
                        <div class="icon me-3 heart-icon">
                            <i class="bx bx-heart"></i>
                            <span class="badge bg-danger">5</span>
                        </div>
                        <?php if ($user_role == 'buyer'): ?>
                            <a href="panier.php" class="icon me-3 cart-icon position-relative">
                                <i class="bx bx-cart"></i>
                                <span class="badge bg-danger">3</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Separator -->
                <hr class="my-1">

                <!-- Navigation Links -->
                <div class="d-flex justify-content-start">
                    <ul class="nav-list d-flex">
                        <li class="nav-item me-5">
                            <a href="afficher_enregistrements.php" class="nav-link">Accueil</a>
                        </li>
                        <li class="nav-item me-5">
                            <a href="" class="nav-link">Boutique</a>
                        </li>
                        <li class="nav-item me-5">
                            <a href="terms_php" class="nav-link">Conditions</a>
                        </li>
                        <li class="nav-item me-5">
                            <a href="propos_du_site.php" class="nav-link">À propos</a>
                        </li>
                        <li class="nav-item me-5">
                            <a href="vous_pouvez_contactez.php" class="nav-link">Contact</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="slides image-container d-flex align-items-center">
            <img src="..\image\255-800x450.jpeg" alt="" class="slide">
        </div>
  
    </header>
     <!-- <div class="shapes">
        <div class="shape" style="top: 10%; left: 20%; animation-delay: 0s;"></div>
        <div class="shape" style="top: 50%; left: 50%; animation-delay: 2s;"></div>
        <div class="shape" style="top: 30%; left: 70%; animation-delay: 4s;"></div>
        <div class="shape" style="top: 80%; left: 10%; animation-delay: 6s;"></div>
        <div class="shape" style="top: 40%; left: 90%; animation-delay: 8s;"></div>
        <div class="shape" style="top: 60%; left: 25%; animation-delay: 3s;"></div>
        <div class="shape" style="top: 70%; left: 75%; animation-delay: 5s;"></div>
        <div class="shape" style="top: 20%; left: 40%; animation-delay: 7s;"></div>
        <div class="shape" style="top: 90%; left: 60%; animation-delay: 9s;"></div>
        <div class="shape" style="top: 40%; left: 10%; animation-delay: 1s;"></div>
    </div>  -->

    </div>

<?php endif; ?>
<div class="container">
     <div class="d-flex justify-content-between align-items-center"></div><br>
    <div class=""></div>
    <form></form> 
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
                                <i class="bx bx-heart"></i>
                            </li>
                            <li class="icon">
                                <i class="bx bx-search"></i>
                            </li>
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
            <!-- <a href="seller.php?seller_id=<?php echo htmlspecialchars($product['seller_id']); ?>">Customer Services</a>
            <a href="">Promotion</a> -->
           
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.4.1/glide.min.js"></script>
<script src="../js/slider.js"></script>
<script src="../js/index.js"></script>

</body>
</html>



















<?php
include 'db.php';
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