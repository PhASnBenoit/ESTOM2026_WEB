<?php
// vérifié par PhA
// razPartie.php
require 'private-estom/db_connect.php';

// Lire le JSON envoyé
$data = json_decode(file_get_contents("php://input"), true);

$stmt = $conn->prepare("UPDATE BOM SET Score = 0, Progression = 0, Remplissage = 0, NbrCollision = 0, Collisions = ''");

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Erreur lors de la mise à jour du score."]);
}
$stmt->close();
$conn->close();
?>
