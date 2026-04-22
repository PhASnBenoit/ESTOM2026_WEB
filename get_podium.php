<?php
// Vérifié par PhA
require 'private-estom/db_connect.php';

$sql = "SELECT Joueur, Score, Couleur FROM BOM ORDER BY Score DESC LIMIT 3";
$result = $conn->query($sql);

$colorNames = [
    0 => "Jaune",
    1 => "Vert",
    2 => "Bleu",
    3 => "Noir"
];

$classement = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $classement[] = [
            'joueur' => htmlspecialchars($row['Joueur']),
            'score' => (int)$row['Score'],
            'couleur' => $colorNames[(int)$row['Couleur']] ?? "Inconnu"
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($classement);
?>
