<?php
session_start();
include 'db.php';
// Vérification de l'authentification de l'utilisateur
// if (!isset($_SESSION['user'])) {
//     // Redirection vers la page de connexion si l'utilisateur n'est pas authentifié
//     header('Location: login.php');
//     exit();
// }

// Récupération des informations de l'utilisateur depuis la session
$user = $_SESSION['user'];

// Vérification si l'utilisateur est un vendeur
if ($user['role'] !== 'seller') {
//     // Redirection vers une autre page si l'utilisateur n'est pas un vendeur
    header('Location: afficher_enregistrements.php'); // Changer le chemin selon vos besoins
   exit();
 }

// Connexion à la base de données
$host = 'localhost';
$dbname = 'ecommerce';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $dbname :" . $e->getMessage());
}

// Récupérer les catégories de la base de données
$stmt = $pdo->prepare('SELECT * FROM categories');
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérification du formulaire soumis
// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     // Vérifier si les champs requis sont présents dans $_POST
//     if (isset($_POST['product_id'], $_POST['seller_email'])) {
//         $product_id = $_POST['product_id'];
//         $seller_email = $_POST['seller_email'];
//         $buyer_name = isset($_POST['name']) ? $_POST['name'] : '';
//         $buyer_email = isset($_POST['email']) ? $_POST['email'] : '';
//         $message = isset($_POST['message']) ? $_POST['message'] : '';

//         // Construction du message
//         $subject = "Demande d'information sur le produit";
//         $body = "Bonjour,\n\n";
//         $body .= "Vous avez reçu une demande d'information concernant votre produit (ID: $product_id) de la part de $buyer_name ($buyer_email).\n";
//         $body .= "Message:\n$message\n\n";
//         $body .= "Cordialement,\n";
//         $body .= "Votre Site";

//         // Envoi de l'email
//         $headers = "From: $buyer_email\r\n";
//         $headers .= "Reply-To: $buyer_email\r\n";
//         $headers .= "X-Mailer: PHP/" . phpversion();

//         if (mail($seller_email, $subject, $body, $headers)) {
//             echo "Votre message a été envoyé avec succès au vendeur.";
//         } else {
//             echo "Erreur lors de l'envoi du message.";
//         }
//     } else {
//         echo "Des champs requis sont manquants.";
//     }
// }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Vendeur</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="../css/seller.css"> 
</head>
<body>
    <div class="container fade-in slide-in">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title">Ajouter un Produit</h2>
                <a href="afficher_enregistrements.php"class=" btn btn-dark "><h2>voir mes produits</h2></a>
                <form action="traitement_formulaire.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="image" class="form-label">Image :</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" capture="camera" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom :</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="text-center mb-3">
                        <label for="price" class="form-label">Prix :</label>
                        <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantité :</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description :</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Catégorie :</label>
                        <select class="form-control w-" id="category_id" name="category_id" required>
                            <option value="">Sélectionner une catégorie</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-plus"></i> Ajouter
                        </button>
                       
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>