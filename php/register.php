<?php
// Inclure le fichier de configuration de la base de données
include 'config.php'; 

// Configuration pour afficher les erreurs PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérification de la soumission du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $numero = isset($_POST['numero']) ? $_POST['numero'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : '';

    // Vérification si les champs requis sont remplis
    if (!empty($username) && !empty($password) && !empty($email) && !empty($numero) && !empty($role)) {
     
            // Créer une instance de PDO pour la connexion à la base de données
            $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//
            // Vérifier si l'utilisateur existe déjà
            $existing_user_query = $db->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
            $existing_user_query->bindParam(':username', $username);
            $existing_user_query->bindParam(':email', $email);
            $existing_user_query->execute();
            $existing_user = $existing_user_query->fetch(PDO::FETCH_ASSOC);

            if ($existing_user) {
                // L'utilisateur existe déjà
                echo json_encode(['status' => 'error', 'message' => 'Username or email already exists']);
            } else {
                // Hachage du mot de passe
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insertion du nouvel utilisateur dans la base de données
                $insert_query = $db->prepare("INSERT INTO users (username, password, email, numero, role) VALUES (:username, :password, :email, :numero, :role)");
                $insert_query->bindParam(':username', $username);
                $insert_query->bindParam(':password', $hashed_password);
                $insert_query->bindParam(':email', $email);
                $insert_query->bindParam(':numero', $numero);
                $insert_query->bindParam(':role', $role);
                $insert_query->execute();

                // Redirection vers la page de connexion
                header('Location: login.php');
                exit();
            }
       
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../cssregister.css">
    <title>Registration</title>
    <style>
        body {
            background-image: url('../image/im.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            animation: slideBackground 20s linear infinite;
            height: 100vh; /* Assurez-vous que le corps occupe toute la hauteur de la fenêtre */
        }

        @keyframes slideBackground {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .container {
            max-width: 500px;
            margin-top: 50px;
        }

        .icon {
            position: absolute;
            margin-left: -30px;
            margin-top: 7px;
        }

        .modal-content {
            animation: fadeIn 1s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .input-group-text, .form-control {
            transition: all 0.3s ease;
        }

        .input-group-text:hover, .form-control:focus {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container text-center">
        <h2 class="mb-4">Welcome</h2>
        <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">Register</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="register-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                        <div class="mb-3 position-relative">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                <input type="text" class="form-control" name="username" placeholder="Username" required>
                            </div>
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" class="form-control" name="password" placeholder="Password" required>
                            </div>
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" class="form-control" name="email" placeholder="Email" required>
                            </div>
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="numero" class="form-label">Numéro de téléphone</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                                <input type="number" class="form-control" name="numero" placeholder="Numéro de téléphone" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="seller">Seller</option>
                                <option value="buyer">Buyer</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-outline-primary w-100">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>