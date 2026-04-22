<?php
require 'private-estom/db_connect.php';

$sql = "SELECT Joueur, Score, Couleur FROM BOM ORDER BY Score DESC LIMIT 3";
$result = $conn->query($sql);

$colorNames = [
    1 => "Vert",
    2 => "Jaune",
    3 => "Noir",
    4 => "Bleu"
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
