<?php
// Vériféee par PhA
require 'private-estom/db_connect.php';

header('Content-Type: application/json');

$tables = ['BOM', 'PAV']; // Ajout des deux tables
$couleurs = [
    0 => "Jaune",
    1 => "Vert",
    2 => "Bleu",
    3 => "Noir"
];

$data = [];

foreach ($tables as $table) {
    $sql = "SELECT IPAddr, Connected, Couleur FROM $table"; // On boucle sur chaque table
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Extraire les deux derniers octets de l'adresse IP
            $ip_parts = explode('.', $row['IPAddr']);
            $short_ip = isset($ip_parts[2]) && isset($ip_parts[3]) ? $ip_parts[2] . '.' . $ip_parts[3] : '??.??';
            //$short_ip = isset($ip_parts[3]) ? $ip_parts[3] : '??';


            $couleur = isset($couleurs[$row['Couleur']]) ? $couleurs[$row['Couleur']] : "Inconnu";
            
            $data[] = [
                'source' => $table, // Indique si l'IP vient de BOM ou PAV
                'couleur' => $couleur,
                'ip' => $short_ip,
                'connected' => (int)$row['Connected']
            ];
        }
    }
}

echo json_encode($data);
$conn->close();
?>
