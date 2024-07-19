<?php
session_start();

// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$dsn = "mysql:host=localhost;dbname=ecommerce;charset=utf8mb4";
$utilisateur = "root";
$mot_de_passe = "";

try {
    $connexion = new PDO($dsn, $utilisateur, $mot_de_passe);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération de l'ID et du rôle de l'utilisateur actuellement connecté
    $user_id = $_SESSION['user']['id'];
    $user_role = $_SESSION['user']['role'];

    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
        $product_id = $_GET['id'];

        // Vérification que le produit appartient au vendeur ou que l'utilisateur est admin
        $requete = $connexion->prepare("SELECT * FROM products WHERE id = ?");
        $requete->execute([$product_id]);
        $product = $requete->fetch();

        if ($product && ($product['seller_id'] == $user_id || $user_role =='admin')) {
            // Affichage du formulaire avec les données actuelles du produit
?>
            <!DOCTYPE html>
            <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Modifier le produit</title>
            </head>
            <body>
            <h1>Modifier le produit</h1>
            <form action="modifier_produit.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                <label for="name">Nom:</label>
                <input type="text" name="name" value="<?php echo $product['name']; ?>" required><br>
                <label for="description">Description:</label>
                <textarea name="description" required><?php echo $product['description']; ?></textarea><br>
                <label for="price">Prix:</label>
                <input type="number" name="price" value="<?php echo $product['price']; ?>" required><br>
                <label for="image">Image:</label>
                <input type="file" name="image"><br>
                <button type="submit">Modifier</button>
            </form>
            </body>
            </html>
<?php
        } else {
            die("Vous n'êtes pas autorisé à modifier ce produit.");
        }
    } elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
        $product_id = $_POST['id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];

        // Vérification que le produit appartient au vendeur ou que l'utilisateur est admin
        $requete = $connexion->prepare("SELECT seller_id FROM products WHERE id = ?");
        $requete->execute([$product_id]);
        $product = $requete->fetch();

        if ($product && ($product['seller_id'] == $user_id || $user_role == 'admin')) {
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_name = $_FILES['image']['name'];
                $image_tmp = $_FILES['image']['tmp_name'];
                move_uploaded_file($image_tmp, "uploads/$image_name");

                $requete = $connexion->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ? WHERE id = ?");
                $requete->execute([$name, $description, $price, $image_name, $product_id]);
            } else {
                $requete = $connexion->prepare("UPDATE products SET name = ?, description = ?, price = ? WHERE id = ?");
                $requete->execute([$name, $description, $price, $product_id]);
            }

            header('Location: afficher_enregistrements.php');
            exit();
        } else {  
            die("Vous n'êtes pas autorisé à modifier ce produit.");
        }
    } else {
        die("Requête invalide.");
    }

} catch (PDOException $erreur) {
    die("La connexion à la base de données a échoué : " . $erreur->getMessage());
}
?>