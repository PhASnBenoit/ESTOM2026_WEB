<?php
header('Content-Type: application/json');
require './private-estom/db_connect.php';

$response = [];

$result = $conn->query("SELECT Status, duree, Temps_restant, Heure_depart FROM Config WHERE id = 1");

if ($result && $row = $result->fetch_assoc()) {
    $statusValue = (int)$row['Status'];
    $statusText = 'stopped';
    switch ($statusValue) {
        case 0: 
            $statusText = 'stopped'; 
            break;
        case 1: 
            $statusText = 'running'; 
            break;
        case 2: 
            $statusText = 'paused'; 
            break;
        case 3: 
            $statusText = 'loading'; 
            break;
    }

    // Par défaut, on prend Temps_restant
    $tempsRestant = (int)$row['Temps_restant'];

    if ($statusText === 'running') {
        // Ne PAS utiliser Temps_restant ici — recalcul dynamique à partir de Heure_depart
        $heureDepart = strtotime($row['Heure_depart']);
        $tempsEcoule = time() - $heureDepart;
        $tempsRestant = max(0, (int)$row['duree'] - $tempsEcoule);
    }

    $response = [
        'status' => $statusText,
        'temps_restant' => $tempsRestant,
        'duree' => (int)$row['duree']
    ];
} else {
    $response = ['error' => 'Impossible de récupérer l’état du timer.'];
}

$conn->close();
echo json_encode($response);
?>
