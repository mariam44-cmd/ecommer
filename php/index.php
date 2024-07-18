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
    //  des données du formulaire
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
                header('location: admin.php');
                exit();
            }

            // Vérification du mot de passe "hacker"
            // if (checkHackerPassword($password)) {
            //     header('location: hacker_page.php');
            //     exit();
            // }

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
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SANIFERE</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.2.0/css/solid.min.css" rel="stylesheet">
<!-- Bootstrap CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.0/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
    body {
        margin: 0;
        padding: 0;
        overflow: hidden;
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(120deg, #efefef 25%, #333333 75%);
        background-size: 200% 200%;
        animation: animateBody 15s ease infinite;
        height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    header {
        position: fixed;
        top: 0;
        width: 100%;
        background: #333333;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 10;
        animation: slideDown 1s ease-in-out;
    }

    header .logo {
        font-size: 20px;
        font-weight: bold;
        letter-spacing: 2px;
        text-transform: uppercase;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4);
        position: relative;
        animation: animateLogo 3s infinite alternate;
    }

    @keyframes animateLogo {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    nav {
        display: flex;
        gap: 20px;
    }

    nav a {
        color: #fefefe;
        text-decoration: none;
        font-size: 16px;
        position: relative;
        transition: color 0.3s ease;
    }

    nav a:hover {
        color: #ff4081;
    }

    nav a::before {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -5px;
        left: 0;
        background-color: #ff4081;
        transition: width 0.3s ease;
    }

    nav a:hover::before {
        width: 100%;
    }

    .container {
        position: relative;
        width: 90%;
        max-width: 1200px;
        margin: auto;
        overflow: hidden;
        background-color: rgba(255, 255, 255, 0.3);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        margin-top: 120px;
        border-radius: 10px;
        backdrop-filter: blur(5px);
        animation: fadeIn 2s ease-in-out;
        padding: 20px;
    }

    .gradient-box {
        position: absolute;
        width: 200%;
        height: 200%;
        top: -50%;
        left: -50%;
        overflow: hidden;
        animation: animateGradient 10s linear infinite;
        opacity: 0.5;
    }

    .gradient-box::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.3), transparent);
    }

    .image-container {
        position: relative;
        width: 100%;
        height: auto;
        overflow: hidden;
    }

    .image-container img {
        width: 100%;
        height: auto;
        object-fit: cover;
        border-radius: 10px;
    }

    .button-container {
        display: flex;
        justify-content: center;
        gap: 20px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .button {
        padding: 15px 30px;
        background-color: #ff4081;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        transition: transform 0.3s ease, background-color 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .button:hover {
        background-color: #e33673;
        transform: translateY(-5px);
    }

    .button:active {
        transform: translateY(2px);
    }

    .button::before {
        content: '';
        position: absolute;
        width: 200%;
        height: 200%;
        background: rgba(255, 255, 255, 0.3);
        top: -50%;
        left: -50%;
        border-radius: 50%;
        transition: all 0.5s ease;
        opacity: 0;
    }

    .button:hover::before {
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 1;
    }

    footer {
        position: fixed;
        bottom: 0;
        width: 100%;
        background: #0f0f0f;
        color: white;
        text-align: center;
        padding: 10px 0;
        box-shadow: 0 -4px 6px rgba(0, 0, 0, 0.1);
        z-index: 10;
        animation: slideUp 1s ease-in-out;
    }

    footer p {
        margin: 0;
        font-size: 24px;
    }

    @keyframes animateBody {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    @keyframes animateGradient {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes animateImage {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    @keyframes slideDown {
        from { transform: translateY(-100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes slideUp {
        from { transform: translateY(100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Responsiveness */
    @media (max-width: 768px) {
        .navbar-nav {
            flex-direction: column;
            align-items: flex-start;
        }
        .navbar-toggler {
            border-color: #fff;
        }
        .container {
            margin-top: 80px;
        }
        .button-container {
            flex-direction: column;
        }
    }
</style>
</head>
<body>
<header>
    <div class="logo">sani<span style="color: #e33673;">F</span>ERE</div>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="propos_du_site.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="vous_pouvez_nous_contact.php">Contact</a>
                </li>
            </ul>
        </div>
    </nav>
</header>

<div class="container">
    <div class="gradient-box"></div>
    <div class="image-container">
        <img src="../image/ECommerce-Fevad-2023-.jpg" alt="Animated Image">
    </div>
    <div class="button-container">
        <button class="button" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
        <a href="conditions.php"><button class="button" >more</button></a>
    </div>
</div>

<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-center" id="loginModalLabel">Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="mb-3 position-relative">
                        <label for="username" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                            <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
                        </div>
                    </div>
                    <div class="mb-3 position-relative">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <label class="form-check-label" for="rememberMe">Vous n'avez pas de compte?</label>
                        <a href="register.php" class="float-end">Inscrivez-vous!</a>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2024 My saifere. Tout pour revendre et racheter.</p>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>