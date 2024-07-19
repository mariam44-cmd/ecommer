

<?php
session_start();

// Vérification de l'authentification de l'administrateur
// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
//          header('Location: login.php');
//      exit();
// }


// Connexion à la base de données
include '../config.php';
include '../db.php';
try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == "delete" && isset($_POST['entity_type']) && $_POST['entity_type'] == "product") {
    $idToDelete = $_POST['id'];
    $sql = "DELETE FROM products WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $idToDelete);

    if ($stmt->execute()) {
        header("Location: admin.php");
        exit();
    } else {
        echo "Erreur lors de la suppression de l'élément.";
    }
}

// Récupération des données des produits
$queryProducts = $db->query("SELECT * FROM products");
$products = $queryProducts->fetchAll(PDO::FETCH_ASSOC);


// Traitement des actions de l'administrateur
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? null;
    $entity_type = $_POST['entity_type'] ?? null;
    $entity_id = $_POST['entity_id'] ?? null;

    if ($action && $entity_type) {
        if ($entity_type == "category") {
            if ($action == "delete" && $entity_id) {
                $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$entity_id]);
            } elseif ($action == "add") {
                $category_name = $_POST['category_name'] ?? '';
                $category_description = $_POST['category_description'] ?? '';
                $stmt = $db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                $stmt->execute([$category_name, $category_description]);
            } elseif ($action == "update" && $entity_id) {
                $category_name = $_POST['category_name'] ?? '';
                $category_description = $_POST['category_description'] ?? '';
                $stmt = $db->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
                $stmt->execute([$category_name, $category_description, $entity_id]);
            }
        } elseif ($entity_type == "user" && $entity_id) {
            if ($action == "delete") {
                $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$entity_id]);
            } elseif ($action == "validate") {
                $stmt = $db->prepare("UPDATE users SET validated = 1 WHERE id = ?");
                $stmt->execute([$entity_id]);
            } elseif ($action == "update") {
                $username = $_POST['username'] ?? '';
                $email = $_POST['email'] ?? '';
                $role = $_POST['role'] ?? '';
                $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
                $stmt->execute([$username, $email, $role, $entity_id]);
            }
        } elseif ($entity_type == "product" && $entity_id) {
            if ($action == "delete") {
                $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$entity_id]);
            } elseif ($action == "validate") {
                $stmt = $db->prepare("UPDATE products SET validated = 1 WHERE id = ?");
                $stmt->execute([$entity_id]);
            } elseif ($action == "update") {
                $name = $_POST['name'] ?? '';
                $description = $_POST['description'] ?? '';
                $price = $_POST['price'] ?? '';
                $image = $_POST['image'] ?? '';
                $seller_id = $_POST['seller_id'] ?? '';
                $stmt = $db->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ?, seller_id = ? WHERE id = ?");
                $stmt->execute([$name, $description, $price, $image, $seller_id, $entity_id]);
            }
        } elseif ($entity_type == "payment" && $entity_id) {
            if ($action == "delete") {
                $stmt = $db->prepare("DELETE FROM payments WHERE id = ?");
                $stmt->execute([$entity_id]);
            } elseif ($action =="validate") {
                $stmt = $db->prepare("UPDATE payments SET validated = 1 WHERE id = ?");
                $stmt->execute([$entity_id]);
            } elseif ($action == "update") {
                $first_name = $_POST['first_name'] ?? '';
                $last_name = $_POST['last_name'] ?? '';
                $username = $POST ['username'] ?? '';
                $email = $POST ['email'] ?? '';
                $address  = $_POST ['address']  ?? '';
                $contry = $_POST ['contry'] ?? '';
                $state = $_POST ['state'] ?? '';
              
                $zip = $POST ['zip'] ?? '';
                $card_name = $_POST ['card_name'] ?? '';
                $credit_card = $_POST ['credit_card'] ?? '';
                $expiration = $_POST ['expiration'] ?? '';
                $cvv = $_POST ['cvv'] ?? '';
                $product_id = $_POST ['product_id'] ?? '';
                $quantity = $_POST['quantity'] ?? '';
                $total_price = $_POST['total_price'] ?? '';
                $transaction = $_POST['transaction_date'] ?? '';
                $stmt = $db->prepare("UPDATE payments , product_id = ?, quantity = ?, total_price = ?, first_name = ?,  last_name = ?, username = ?, email = ?, address = ?,    state = ?, zip = ?,  card_name = ?, credit_card = ? , expiration = ?, ext_year = ?, cvv = ?, transaction_date = ?  WHERE id = ?");
                 $stmt->execute([ $product_id , $quantity , $total_price , $first_name ,  $last_name , $username , $email , $address ,   $state , $zip ,  $card_name , $credit_card , $expiration , $ext_year , $cvv , $transaction_date, $entity_id]);
            }
        } elseif ($entity_type == "comment" && $entity_id) {
            if ($action == "delete") {
                $stmt = $db->prepare("DELETE FROM comments WHERE id = ?");
                $stmt->execute([$entity_id]);
            } elseif ($action == "validate") {
                $stmt = $db->prepare("UPDATE comments SET validated = 1 WHERE id = ?");
                $stmt->execute([$entity_id]);
            } elseif ($action == "update") {
              
                $product_id = $_POST['product_id'] ?? '';
                $user_id = $_POST['user_id'] ?? '';
                $comment_date = $_POST['comment'] ?? '';
                $comment_date = $_POST['created_at'] ?? '';
                $stmt = $db->prepare("UPDATE comments SET  product_id = ?, user_id = ?, comment = ?, created_at = ?, WHERE id = ?");
                $stmt->execute([ $product_id, $user_id, $comment, $created_at, $entity_id]);
            }
        } elseif ($entity_type == "cart_items" && $entity_id) {
            if ($action == "delete") {
                $stmt = $db->prepare("DELETE FROM cart_items WHERE id = ?");
                $stmt->execute([$entity_id]);
            } elseif ($action == "validate") {
                $stmt = $db->prepare("UPDATE cart_items SET validated = 1 WHERE id = ?");
                $stmt->execute([$entity_id]);
            } elseif ($action =="update") {
                $user_id = $_POST['user_id'] ?? '';
                $product_id = $_POST['product_ids'] ?? '';
                $quantities = $_POST['quantities'] ?? '';
                $stmt = $db->prepare("UPDATE cart_items SET user_id = ?, product_id = ?, quantities = ? WHERE id = ?");
                $stmt->execute([$user_id, $product_id, $quantities, $entity_id]);
            }
        }
        header('Location: admin.php');
        exit();
    }
}



// Récupération des données
$queryUsers = $db->query("SELECT * FROM users");
$users = $queryUsers->fetchAll(PDO::FETCH_ASSOC);

$queryProducts = $db->query("SELECT * FROM products");
$products = $queryProducts->fetchAll(PDO::FETCH_ASSOC);

$queryComments = $db->query("SELECT * FROM comments");
$comments = $queryComments->fetchAll(PDO::FETCH_ASSOC);

$queryCategories = $db->query("SELECT * FROM categories");
$categories = $queryCategories->fetchAll(PDO::FETCH_ASSOC);

$queryPayments = $db->query("SELECT * FROM payments");
$payments = $queryPayments->fetchAll(PDO::FETCH_ASSOC);

$querycart_items = $db->query("SELECT * FROM cart_items");
$cart_items = $queryPayments->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../css/liste.css">
    <script src="https://unpkg.com/feather-icons"></script>
  

    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap");

        * {
            margin: 0;
            padding: 0;
            outline: none;
            border: none;
            text-decoration: none;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            background-image: url('../image/255-800x450.jpeg');
            background-size: cover;
          
           
             background-attachment: fixed;
            
            animation: bgAnimation 20s infinite linear;
        }

        @keyframes bgAnimation {
            0% {
                background-position: 0 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0 50%;
            }
        }

       
        .fade-in {
            animation: fadeIn 1s ease-in-out;
        }

        .slide-in {
            animation: slideIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .card {
            margin-top: 20px;
        }

        .text-center {
            text-align: center;
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


        nav {
            position: absolute;
            top: 0;
            bottom: 0;
            height: 7100px;
            left: 0;
            background: #333;
            width: 50px;
            overflow: hidden;
            transition: width 0.2s linear;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.1);
        }
        

        .logo {
            text-align: center;
            display: flex;
            align-items: center;
            padding: 10px;
            transition: all 0.5s ease;
        }

        .logo img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
        }

        .logo span {
            font-weight: 600;
            padding-left: 15px;
            font-size: 18px;
            text-transform: uppercase;
        }

        nav ul {
            list-style: none;
        }

        nav ul li {
            width: 100%;
        }

        nav ul li a {
            position: relative;
            color: rgb(85, 83, 83);
            font-size: 13px;
            display: table;
            width: 100%;
            padding: 20px;
            text-align: center;
            color: #fff;
        }

        nav ul li a .icon {
            width: 20px;
            height: 20px;
            display: inline-block;
            margin-right: 10px;
            vertical-align: middle;
        }


        nav:hover {
            width: 280px;
            transition: all 0.5s ease;
        }

        .logout {
            position: absolute;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li>
                <a href="" class="logo">
                    <img src="..\image\logo.png" alt="" width="200px"/>
                    <span class="nav-item">SaniFRE</span>
                </a>
            </li>
            <li>
                <a href="admin.php">
                    <i data-feather="home" class="icon"></i>
                    <span class="nav-item">home</span>
                </a>
            </li>
            <li>
                <a href="#idUsers">
                    <i data-feather="user" class="icon"></i>
                    <span class="nav-item">utilisateur</span>
                </a>
            </li>
            <li>
                <a href="#idProducts">
                    <i data-feather="book" class="icon"></i>
                    <span class="nav-item">Produits</span>
                </a>
            </li>
            <li>
                <a href="#idComments">
                    <i data-feather="clipboard" class="icon"></i>
                    <span class="nav-item"produits>Commentaires</span>
                </a>
            </li>
            <li>
                <a href="#idCategories">
                    <i data-feather="mail" class="icon"></i>
                    <span class="nav-item">Categories</span>
                </a>
            </li>
            <li>
            <a href="#idPayements">
                    <i data-feather="mail" class="icon"></i>
                    <span class="nav-item">payments</span>
                </a>
            </li>
     >
            <li>
            <a href="#idAajouterU">
                    <i data-feather="mail" class="icon"></i>
                    <span class="nav-item">Ajouter un utilisateur</span>
                </a>
            </li>
       
            <li>
            <a href="#idAjouterC">
                    <i data-feather="mail" class="icon"></i>
                    <span class="nav-item">Ajouter une categorie</span>
                </a>
            </li>
            <li>
                <a href="#" class="logout">
                    <i data-feather="log-out" class="icon"></i>
                    <span class="nav-item">Log out</span>
                </a>
            </li>
        </ul>
    </nav>
   

  
<div class="container fade-in slide-in mt-5">

<a href="afficher_commandes.php" class="btn btn-info my-2">Voir les commandes</a>

<h1 class="my-4">Admin Page</h1>

<!-- Liste des Utilisateurs -->
<h2 id="idUsers">Liste des Utilisateurs</h2>
<table class="table table-striped table-hover"> 
    <thead class="thead-dark">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
    <tr>
        <td><?php echo htmlspecialchars($user['id']); ?></td>
        <td><?php echo htmlspecialchars($user['username']); ?></td>
        <td><?php echo htmlspecialchars($user['email']); ?></td>
        <td><?php echo htmlspecialchars($user['role']); ?></td>
        <td>
            <form action="modifier_utlisateur.php" method="POST" style="display:inline;">
                <input type="hidden" name="entity_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                <input type="hidden" name="entity_type" value="user">
                <input type="hidden" name="action" value="modifier">
                <button type="submit" class="btn btn-success btn-sm">modifier</button>
               
            </form>
             
            
            <form action="supprimer_utilisateur.php" method="POST" style="display:inline;"> 
                <input type="hidden" name="entity_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                <input type="hidden" name="entity_type" value="user">
                <input type="hidden" name="action" value="delete">
                <button type="submit" name="delete_user" class="btn btn-danger btn-sm">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
<!-- Liste des Products -->
<h2 id="idProducts">Liste des Produits</h2>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Description</th>
            <th>Prix</th>
            <th>Image</th>
            <th>ID Vendeur</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <tbody>
    <?php foreach ($products as $product): ?>
    <tr>
        <td><?php echo $product['id']; ?></td>
        <td><?php echo $product['name']; ?></td>
        <td><?php echo $product['description']; ?></td>
        <td><?php echo $product['price']; ?></td>
        <td><img src="uploaded_images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" width="50"></td>
        <td><?php echo $product['seller_id']; ?></td>
        <td>
            <form action="" method="POST" style="display:inline;">
                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="entity_type" value="product">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn btn--outline-danger"><i class="fas fa-trash-alt"></i> Supprimer</button>
    </form>
  
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

<!-- Liste des Comments -->
<h2 id="idComments">Liste des Commentaires</h2>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Contenu</th>
            <th>ID Produit</th>
            <th>ID Utilisateur</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($comments as $comment): ?>
    <tr>
        <td><?php echo htmlspecialchars($comment['id']); ?></td>
      
        <td><?php echo htmlspecialchars($comment['product_id']); ?></td>
        <td><?php echo htmlspecialchars($comment['user_id']); ?></td>
        <td><?php echo htmlspecialchars($comment['comment']); ?></td>
        <td><?php echo htmlspecialchars($comment['created_at']); ?></td>
      
        <td>
            <form action="" method="POST" style="display:inline;">
                <input type="hidden" name="entity_id" value="<?php echo htmlspecialchars($comment['id']); ?>">
                <input type="hidden" name="entity_type" value="comment">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
            </form>
            
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

<!-- Liste des Catégories -->
<h2 id="idCategories">Liste des Catégories</h2>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Description</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($categories as $category): ?>
    <tr>
        <td><?php echo htmlspecialchars($category['id']); ?></td>
        <td><?php echo htmlspecialchars($category['name']); ?></td>
        <td><?php echo htmlspecialchars($category['description']); ?></td>
        <td>
            <form action="admin.php" method="POST" style="display:inline;">
                <input type="hidden" name="entity_id" value="<?php echo htmlspecialchars($category['id']); ?>">
                <input type="hidden" name="entity_type" value="category">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
            </form>
            <form action="admin.php" method="POST" style="display:inline;">
                <input type="hidden" name="entity_id" value="<?php echo htmlspecialchars($category['id']); ?>">
                <input type="hidden" name="entity_type" value="category">
                <input type="hidden" name="action" value="update">
                <input type="text" name="category_name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                <input type="text" name="category_description" value="<?php echo htmlspecialchars($category['description']); ?>" required>
                <button type="submit" class="btn btn-warning btn-sm">Mettre à jour</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

<!-- Liste des Payments -->



 <h2 id="idPayements">Liste des Paiements</h2>
 <div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Prénom</th>
                <th>Nom</th>
                <th>Nom d'utilisateur</th>
                <th>Email</th>
                <th>Adresse</th>
                <th>État</th>
                <th>Code Postal</th>
                <th>Nom sur la carte</th>
                <th>Numéro de carte de crédit</th>
                <th>Date d'expiration</th>
                <th>CVV</th>
                <th>Date de transaction</th>
                <th>ID Produit</th>
                <th>Quantité</th>
                <th>Prix total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($payments as $payment): ?>
        <tr>
            <td><?php echo htmlspecialchars($payment['id']); ?></td>
            <td><?php echo htmlspecialchars($payment['first_name']); ?></td>
            <td><?php echo htmlspecialchars($payment['last_name']); ?></td>
            <td><?php echo htmlspecialchars($payment['username']); ?></td>
            <td><?php echo htmlspecialchars($payment['email']); ?></td>
            <td><?php echo htmlspecialchars($payment['address']); ?></td>
            <td><?php echo htmlspecialchars($payment['state']); ?></td>
            <td><?php echo htmlspecialchars($payment['zip']); ?></td>
            <td><?php echo htmlspecialchars($payment['name_on_card']); ?></td>
            <td><?php echo htmlspecialchars($payment['credit_card_number']); ?></td>
            <td><?php echo htmlspecialchars($payment['expiration']); ?></td>
            <td><?php echo htmlspecialchars($payment['cvv']); ?></td>
            <td><?php echo htmlspecialchars($payment['transaction_date']); ?></td>
            <td><?php echo htmlspecialchars($payment['product_id']); ?></td>
            <td><?php echo htmlspecialchars($payment['quantity']); ?></td>
            <td><?php echo htmlspecialchars($payment['total_price']); ?></td>
            <td>
                <form action="admin.php" method="POST" style="display:inline;">
                    <input type="hidden" name="entity_id" value="<?php echo htmlspecialchars($payment['id']); ?>">
                    <input type="hidden" name="entity_type" value="payment">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                    <button type="submit" formaction="admin.php" formmethod="POST" name="action" value="validate" class="btn btn-success btn-sm">Valider</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
</div>

<!-- Ajout d'une catégorie (formulaire exemple) -->
<h2 id="idAjouterC" >Ajouter une Catégorie</h2>
<form action="admin.php" method="POST" class="my-4">
    <input type="hidden" name="entity_type" value="category">
    <input type="hidden" name="action" value="add">
    <div class="mb-3">
        <label for="category_name" class="form-label">Nom de la catégorie</label>
        <input type="text" class="form-control" id="category_name" name="category_name">
    </div>
    <div class="mb-3">
        <label for="category_description" class="form-label">Description</label>
        <input type="text" class="form-control" id="category_description" name="category_description">
    </div>
    <button type="submit" class="btn btn-primary">Ajouter</button>
</form><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

    </div>

    <script>
        feather.replace();
    </script>
</body>
</html>