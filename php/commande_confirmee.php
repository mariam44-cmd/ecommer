<?php
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $address = htmlspecialchars($_POST['address']);
    $country = htmlspecialchars($_POST['country']);
    $state = htmlspecialchars($_POST['state']);
    $zip = htmlspecialchars($_POST['zip']);
    $name_on_card = htmlspecialchars($_POST['name_on_card']);
    $credit_card_number = htmlspecialchars($_POST['credit_card_number']);
    $expiration = htmlspecialchars($_POST['expiration']);
    $cvv = htmlspecialchars($_POST['cvv']);

    // Fixed values for quantity and price per unit
    $quantity = 1; // Fixed quantity for this example
    $price_per_unit = 10; // Replace with your own method to get price per unit

    // Calculate total price
    $total_price = $price_per_unit * $quantity;

    try {
        // Connect to database
        $connexion = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
        $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Perform payment (assuming quantity is always available)
        // Insert payment details into payments table
        $sql = "INSERT INTO payments (first_name, last_name, username, email, address, country, state, zip, name_on_card, credit_card_number, expiration, cvv, quantity, total_price)
                VALUES (:first_name, :last_name, :username, :email, :address, :country, :state, :zip, :name_on_card, :credit_card_number, :expiration, :cvv, :quantity, :total_price)";
        $requete_insertion = $connexion->prepare($sql);

        // Bind parameters
        $requete_insertion->bindParam(':first_name', $first_name);
        $requete_insertion->bindParam(':last_name', $last_name);
        $requete_insertion->bindParam(':username', $username);
        $requete_insertion->bindParam(':email', $email);
        $requete_insertion->bindParam(':address', $address);
        $requete_insertion->bindParam(':country', $country);
        $requete_insertion->bindParam(':state', $state);
        $requete_insertion->bindParam(':zip', $zip);
        $requete_insertion->bindParam(':name_on_card', $name_on_card);
        $requete_insertion->bindParam(':credit_card_number', $credit_card_number);
        $requete_insertion->bindParam(':expiration', $expiration);
        $requete_insertion->bindParam(':cvv', $cvv);
        $requete_insertion->bindParam(':quantity', $quantity);
        $requete_insertion->bindParam(':total_price', $total_price);

        // Execute the query
        $requete_insertion->execute();

        // Update success message
        $payment_success_message = "Paiement effectué avec succès";

    } catch (PDOException $e) {
        die("Erreur PDO : " . $e->getMessage());
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Paiement</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
        }

        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            animation: fadeInAnimation 1s ease forwards;
        }

        .form-container h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: #333333;
            text-transform: uppercase;
        }

        .form-container form {
            animation: slideInAnimation 1s ease forwards;
        }

        .form-container .form-group {
            margin-bottom: 20px;
        }

        .form-container label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555555;
            font-size: 16px;
        }

        .form-container input[type="text"],
        .form-container input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #cccccc;
            border-radius: 4px;
            transition: border-color 0.3s ease;
            font-size: 16px;
            color: #333333;
        }

        .form-container input[type="text"]:focus,
        .form-container input[type="email"]:focus {
            outline: none;
            border-color: #6cb2eb;
        }

        .form-container input[type="submit"] {
            background-color: #007bff;
            color: #ffffff;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-transform: uppercase;
            transition: background-color 0.3s ease;
        }

        .form-container input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .success-message {
            margin-top: 20px;
            padding: 10px;
            background-color: #dff0d8;
            border: 1px solid #c3e6cb;
            color: #155724;
            border-radius: 4px;
            animation: slideInAnimation 1s ease forwards;
        }

        @keyframes fadeInAnimation {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInAnimation {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Paiement</h1>
        <?php if (isset($payment_success_message)): ?>
            <div class="success-message">
                <?php echo $payment_success_message; ?>
            </div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="first_name">Prénom:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Nom:</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="username">Nom d'utilisateur:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="address">Adresse:</label>
                <input type="text" id="address" name="address" required>
            </div>
            <div class="form-group">
                <label for="country">Pays:</label>
                <input type="text" id="country" name="country" required>
            </div>
            <div class="form-group">
                <label for="state">État:</label>
                <input type="text" id="state" name="state" required>
            </div>
            <div class="form-group">
                <label for="zip">Code Postal:</label>
                <input type="text" id="zip" name="zip" required>
            </div>
            <div class="form-group">
                <label for="name_on_card">Nom sur la carte:</label>
                <input type="text" id="name_on_card" name="name_on_card" required>
            </div>
            <div class="form-group">
                <label for="credit_card_number">Numéro de carte de crédit:</label>
                <input type="text" id="credit_card_number" name="credit_card_number" required>
            </div>
            <div class="form-group">
                <label for="expiration">Date d'expiration:</label>
                <input type="text" id="expiration" name="expiration" required>
            </div>
            <div class="form-group">
                <label for="cvv">CVV:</label>
                <input type="text" id="cvv" name="cvv" required>
            </div>
         
            <input type="hidden" id="quantity" name="quantity" value="1">
            <input type="submit" value="Payer">
        </form>
    </div>
</body>
</html>