<?php
// vérifié par PhA
require 'private-estom/db_connect.php';

header('Content-Type: application/json');

// Récupérer les données reçues
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["ip"]) || !isset($data["collisions"])) {
    echo json_encode(["error" => "Données invalides"]);
    exit;
}

$ip = $data["ip"];
$newCollisions = $data["collisions"];

// **Récupérer les collisions actuelles**
$sqlSelect = "SELECT Collisions FROM BOM WHERE IPAddr = ?";
$stmtSelect = $conn->prepare($sqlSelect);
$stmtSelect->bind_param("s", $ip);
$stmtSelect->execute();
$result = $stmtSelect->get_result();
$row = $result->fetch_assoc();
$stmtSelect->close();

$existingCollisions = json_decode($row['Collisions'], true) ?? [];

// **Ne pas ajouter des collisions en double**
foreach ($newCollisions as $collision) {
    $alreadyExists = false;
    foreach ($existingCollisions as $existingCollision) {
        if ($existingCollision['x'] == $collision['x'] && $existingCollision['y'] == $collision['y']) {
            $alreadyExists = true;
            break;
        } // if
    } // foreach exis
    if (!$alreadyExists) {
        $existingCollisions[] = $collision;
    } // if
} // foreach newcoll

// Mise à jour des collisions dans la base
$collisionsJson = json_encode($existingCollisions);
$sqlUpdate = "UPDATE BOM SET Collisions = ? WHERE IPAddr = ?";
$stmtUpdate = $conn->prepare($sqlUpdate);
$stmtUpdate->bind_param("ss", $collisionsJson, $ip);
$success = $stmtUpdate->execute();

if ($success) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Échec de l'enregistrement"]);
}

$stmtUpdate->close();
$conn->close();
?>
