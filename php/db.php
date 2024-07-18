<?php 
 try{
    // Connexion à la bdd
    $connexion = new PDO('mysql:host=localhost;dbname=ecommerce', 'root','');
    
} catch (PDOException $e){
    echo 'Erreur : '. $e->getMessage();
    
}
?>
<?php
$db_host = 'localhost';
$db_name = 'ecommerce';
$db_user = 'root';
$db_password = '';
?>