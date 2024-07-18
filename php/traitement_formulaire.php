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
            
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = array('jpg', 'jpeg', 'png');

            // Vérifier si l'extension du fichier est autorisée
            if (in_array($fileExtension, $allowedExtensions)) {
                $uploadDir = 'uploaded_images/';
                $uniqueFileName = uniqid('image_') . '.' . $fileExtension;
                $destination = $uploadDir . $uniqueFileName;

                // Déplacer le fichier téléchargé vers le répertoire de destination
                if (move_uploaded_file($fileTmpPath, $destination)) {
                    // Insertion du produit dans la base de données avec le nom unique du fichier
                    $requete = $connexion->prepare('INSERT INTO products (name, description, price, quantity, image, category_id, seller_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
                    $requete->execute([$name, $description, $price, $quantity, $uniqueFileName, $category_id, $seller_id]);

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