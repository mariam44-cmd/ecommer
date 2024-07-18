<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'C:/wamp64/www/ecommerce/php/vendor/autoload.php'; // Utiliser un chemin absolu

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $seller_email = $_POST['seller_email'];
    $seller_name = $_POST['seller_name'];
    $buyer_name = $_POST['name'];
    $buyer_email = $_POST['email'];
    $message = $_POST['message'];

    // Construction du message
    $subject = "Demande d'information de la part d'un acheteur";
    $body = "Bonjour $seller_name,\n\n";
    $body .= "Vous avez reçu une demande d'information de la part de $buyer_name ($buyer_email).\n";
    $body .= "Message:\n$message\n\n";
    $body .= "Cordialement,\n";
    $body .= "Votre Site";

    // Configuration de PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Utilisez le serveur SMTP de votre fournisseur de messagerie
        $mail->SMTPAuth = true;
        $mail->Username = 'mariamadjires@gmail.com'; // Remplacez par votre adresse email
        $mail->Password = 'jeconaispas'; // Remplacez par votre mot de passe ou mot de passe d'application
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Destinataires
        $mail->setFrom($buyer_email, $buyer_name);
        $mail->addAddress($seller_email, $seller_name);

        // Contenu de l'email
        $mail->isHTML(false); // Format de l'email
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        echo "Votre message a été envoyé avec succès au vendeur.";
    } catch (Exception $e) {
        echo "Erreur lors de l'envoi du message. Erreur: {$mail->ErrorInfo}";
    }
}
?>






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

    // Récupérer les informations de l'utilisateur, y compris product_id et cart_id
    $requeteUser = $connexion->prepare("SELECT * FROM users WHERE id = :user_id");
    $requeteUser->execute([':user_id' => $user_id]);
    $user_info = $requeteUser->fetch(PDO::FETCH_ASSOC);

    // Récupérer les catégories
    $requeteCategories = $connexion->prepare("SELECT * FROM categories");
    $requeteCategories->execute();
    
    $requeteProducts = $connexion->prepare("SELECT * FROM products");
    $requeteProducts->execute();

    $categories = $requeteCategories->fetchAll();
    $sql = "SELECT * FROM products WHERE quantity > 0";
    $params = [];
    // Récupérer les produits en fonction du rôle de l'utilisateur
   
    // Ajouter les conditions spécifiques selon le rôle de l'utilisateur
    
    $sql = "SELECT * FROM products";
    $params = [];

    // Ajouter les conditions spécifiques selon le rôle de l'utilisateur
    if ($user_role == 'seller') {
        // Pour les vendeurs, récupérer les produits créés par le vendeur
        $sql .= " WHERE seller_id = :seller_id";
        $params[':seller_id'] = $user_id;
        
        

    } elseif ($user_role == 'admin' || $user_role == 'buyer') {
        if (isset($_GET['category_id']) && $_GET['category_id'] != '') {
            $sql .= " WHERE category_id = :category_id";
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

    // Récupérer les commentaires pour chaque produit (facultatif, décommenter si nécessaire)
    // foreach ($products as &$product) {
    //     $requeteComments = $connexion->prepare("SELECT * FROM comments WHERE product_id = :product_id");
    //     $requeteComments->bindParam(':product_id', $product['id']);
    //     $requeteComments->execute();
    //     $product['comments'] = $requeteComments->fetchAll(PDO::FETCH_ASSOC);
    // }
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
<body>
    

<div class="hed">
    <?php if ($user_role == 'seller'): ?>
                            <a href="ordersV.php" class="btn btn-info my-2">Voir les commandes</a> 
                            <!-- <a href="afficher_commandes?buyer_id=<?php echo htmlspecialchars($orders['buyer_id']); ?>" class="btn btn-outline-primary">commande fait </a> --> 
                              <?php endif; ?>
<?php if ($user_role == 'buyer'): ?>
    

    <header class="header" id="header">
    <div class="navigation">
        <div class="">
            <!-- Logo, Search Bar, and Icons on the Same Line -->
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
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

            <!-- Separator -->
            <hr class="my-1">

            <!-- Navigation Links -->
            <div class="d-flex justify-content-start flex-wrap">
                <ul class="nav-list d-flex flex-wrap">
                    <li class="nav-item me-5 mb-2">
                        <a href="afficher_enregistrements.php" class="nav-link">Accueil</a>
                    </li>
                    <li class="nav-item me-5 mb-2">
                        <a href="" class="nav-link">Boutique</a>
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
    

        <div class="slides image-container d-flex align-items-center">
            <img src="..\image\255-800x450.jpeg" alt="" class="">
        </div>  
       <!<div class="shapes">
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
        <div class="shape" style="top: 10%; right: 20%; animation-delay: 0s;"></div>
        <div class="shape" style="top: 50%; right: 50%; animation-delay: 2s;"></div>
        <div class="shape" style="top: 30%; right: 70%; animation-delay: 4s;"></div>
        <div class="shape" style="top: 80%; right: 10%; animation-delay: 6s;"></div>
        <div class="shape" style="top: 40%; right: 90%; animation-delay: 8s;"></div>
        <div class="shape" style="top: 60%; right: 25%; animation-delay: 3s;"></div>
        <div class="shape" style="top: 70%; right: 75%; animation-delay: 5s;"></div>
        <div class="shape" style="top: 20%; right: 40%; animation-delay: 7s;"></div>
        <div class="shape" style="top: 90%; right: 60%; animation-delay: 9s;"></div>
        <div class="shape" style="top: 40%; right: 10%; animation-delay: 1s;"></div>

        
    </div>  

    </div>
    </header>
<?php endif; ?>
<div class="container">
     <!-- <div class="d-flex justify-content-between align-items-center"></div><br>
    <div class=""></div>
    <form></form>  -->
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