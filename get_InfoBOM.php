<?php
// vérifié par PhA

require 'private-estom/db_connect.php';

header('Content-Type: application/json');


// Vérification de connexion
if (!$conn || $conn->connect_error) {
    die(json_encode(["error" => "Erreur de connexion à la base de données"]));
}

// Correspondance entre le nom de la couleur et l'entier dans la base de données
$colorMap = [
    "Jaune" => 0,
    "Vert" => 1,
    "Bleu" => 2,
    "Noir" => 3
];

$couleur = $_GET['couleur'] ?? '';  // Récupère la couleur du camion demandée
if (!isset($colorMap[$couleur])) {
    echo json_encode(["error" => "Couleur invalide"]);
    exit;
}

$couleurDB = $colorMap[$couleur]; // Convertit en entier pour la requête SQL

$sql = "SELECT IPAddr, Progression, NbrCollision, Joueur, Collisions  FROM BOM WHERE Couleur = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $couleurDB);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $collisions = json_decode($row['Collisions'], true) ?? [];

    if ((int)$row['NbrCollision'] === 0) {
        $collisions = [];
        $sqlUpdate = "UPDATE BOM SET Collisions = NULL WHERE IPAddr = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("s", $row['IPAddr']);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }
    // Ajouter un index unique pour chaque collision (evite les doublons)
    foreach ($collisions as $key => $collision) {
        $collisions[$key]['index'] = $key;  // L'index est basé sur la clé dans le tableau JSON
    }
    $data[] = [
        "ip" => $row['IPAddr'],
        "progression" => (float)$row['Progression'],
        "nbrCollision" => (int)$row['NbrCollision'],
        "joueur" => $row['Joueur'] ?? "",
        "collisions" => $collisions
    ];
}

echo json_encode($data);

$stmt->close();
$conn->close();
?>
