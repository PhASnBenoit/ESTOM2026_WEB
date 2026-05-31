<?php
session_start();

// Sécurité : vérification méthode POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit();
}

// Vérification format (5 chiffres)
if (!isset($_POST['password']) || !preg_match('/^[0-9]{5}$/', $_POST['password'])) {
    header("Location: index.php?error=1");
    exit();
}

$password_user = $_POST['password'];

require_once "private-estom/db_connect.php";

// Vérifier connexion
if ($conn->connect_error) {
    die("Erreur connexion BDD : " . $conn->connect_error);
}

// Requête préparée
$stmt = $conn->prepare("SELECT password FROM Config LIMIT 1");
if (!$stmt) {
    die("Erreur préparation : " . $conn->error);
} // if
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $password_db = $row['password'];
    // ⚠️ Cas 1 : mot de passe hashé (recommandé)
    /*
    if (password_verify($password_user, $password_db)) {
        session_regenerate_id(true);
        $_SESSION['auth'] = true;
        header("Location: menu.php");
        exit();
    }
    */
    // ⚠️ Cas 2 : mot de passe en clair
    if ($password_user === $password_db) {
        session_regenerate_id(true);
        $_SESSION['auth'] = true;
        header("Location: menu.php");
        exit();
    } // if
} // if $row

$stmt->close();
$conn->close();

// Échec
header("Location: index.php?error=1");
exit();
