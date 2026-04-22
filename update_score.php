<?php
// vérifié par PhA
require 'private-estom/db_connect.php';

// Lire le JSON envoyé
$data = json_decode(file_get_contents("php://input"), true);

$ip = $data['ip'] ?? null;
$score = $data['score'] ?? null;

if (!$ip || $score === null) {
    http_response_code(400);
    echo json_encode(["error" => "IP ou score manquant."]);
    exit;
}

$stmt = $conn->prepare("UPDATE BOM SET Score = ? WHERE IPAddr = ?");
$stmt->bind_param("is", $score, $ip);
//echo "COUCOU ";
//die();

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Erreur lors de la mise à jour du score."]);
}

$stmt->close();
$conn->close();
?>
