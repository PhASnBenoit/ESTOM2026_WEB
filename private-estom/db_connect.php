<?php
// Configuration de la base de données
$host = 'localhost';
$username = 'SuperViseur';
$password = 'SuperViseur-estom_2026';
$dbname = 'ESTOM';

// Création d'un objet MySQLi
$conn = new mysqli($host, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}
?>
