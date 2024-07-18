<?php
include 'db.php'; // Assurez-vous que ce fichier inclut les informations de connexion à la base de données

try {
    // Connexion à la base de données
    $connexion = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ajout des colonnes et des clés étrangères
    $sql = "
        ALTER TABLE users
        ADD COLUMN cart_id INT NOT NULL,
        ADD COLUMN product_id INT NOT NULL,
        ADD CONSTRAINT fk_cart_id FOREIGN KEY (cart_id) REFERENCES cart(id),
        ADD CONSTRAINT fk_product_id FOREIGN KEY (product_id) REFERENCES products(id);
    ";
    
    // Exécution de la commande SQL
    $connexion->exec($sql);

    echo "Colonnes et clés étrangères ajoutées avec succès.";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
