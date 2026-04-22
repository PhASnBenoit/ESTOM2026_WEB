<?php
// véérifié par PhA
// get_heure_depart.php
header('Content-Type: application/json');

// Connexion à la base de données
require './private-estom/db_connect.php';

if (!$conn) {
    echo json_encode(['error' => 'Erreur de connexion à la base de données']);
    exit;
}

$query = "SELECT Heure_depart, duree FROM Config LIMIT 1";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    if ($row && isset($row['Heure_depart'])) {
        $heure_depart = date('c', strtotime($row['Heure_depart']));
        $duree = isset($row['duree']) ? intval($row['duree']) : null;
        echo json_encode([
            'heure_depart' => $heure_depart,
            'duree' => $duree
        ]);
    } else {
        echo json_encode(['error' => 'Heure_depart non trouvée']);
    }
    mysqli_free_result($result);
} else {
    echo json_encode(['error' => 'Erreur lors de la requête SQL']);
}

mysqli_close($conn);
?>
