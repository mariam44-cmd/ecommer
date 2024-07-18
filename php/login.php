<?php
include 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db.php';

// Fonction pour vérifier le mot de passe "hacker"
function checkHackerPassword($password) {
    // Liste des mots de passe "hacker" autorisés
    $allowedPasswords = array('hacker', '123456'); // Ajoutez d'autres mots de passe si nécessaire
    return in_array($password, $allowedPasswords);
}

// Vérification de la soumission du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $usernameOrEmail = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Vérification si les champs requis sont remplis
    if (!empty($usernameOrEmail) && !empty($password)) {
        // Connexion à la base de données
        $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Préparation de la requête SQL pour vérifier les informations d'identification
        $query = $db->prepare("SELECT * FROM users WHERE email = :email");
        $query->bindParam(':email', $usernameOrEmail);
        $query->execute();
        $user = $query->fetch(PDO::FETCH_ASSOC);

        // Vérification si l'utilisateur existe et si le mot de passe est correct
        if ($user && password_verify($password, $user['password'])) {
            // Authentification réussie
            session_start();
            $_SESSION['user'] = $user;

            // Vérification spécifique pour l'email et le mot de passe admin
            if ($usernameOrEmail == 'mariamdjiree@gmail.com' && $password == '014810') {
                header('location: admin_page.php');
                exit();
            }

            // Vérification du mot de passe "hacker"
            if (checkHackerPassword($password)) {
                header('location: hacker_page.php');
                exit();
            }

            // Redirection en fonction du rôle de l'utilisateur
            if ($user['role'] == 'seller') {
                header('location: seller_page.php?id='.$user['id']);
                exit();
            } else if ($user['role'] == 'buyer') {
                header('location: afficher_enregistrements.php?id='.$user['id']);
                exit();
            }
        } else {
            // Affichage du message d'erreur en cas de mot de passe incorrect
            echo "Identifiants invalides.";
        }
    } else {
        // Affichage du message d'erreur si des champs sont manquants
        echo "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background-image: url('../image/meilleur-site-ecommerce.jpg');
            background-repeat: no-repeat;
            width: 100%;
            background-size: cover;
            background-attachment: fixed;
            opacity: 20px;
        }
        .container {
            max-width: 500px;
            margin-top: 200px;
        }
        .icon {
            position: absolute;
            margin-left: -30px;
            margin-top: 7px;
        }
        .form-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background: transparent;
            backdrop-filter: blur(200px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-box">
            <h2 class="text-center">Login</h2>
            <form id="login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="mb-3 position-relative">
                    <label for="usernameOrEmail" class="form-label">Username or Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                        <input type="text" class="form-control" name="username" id="usernameOrEmail" placeholder="Username or Email" required>
                    </div>
                </div>
                <div class="mb-3 position-relative">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="rememberMe">
                <label class="form-check-label" for="rememberMe">Vous n'avez pas de compte ?</label>
                <a href="register.php" class="float-end">Inscrivez-vous</a>
            </div>
        </div>
    </div>
</body>
</html>