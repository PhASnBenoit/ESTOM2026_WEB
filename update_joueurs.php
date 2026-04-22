<?php
// Vérifié par PhA
require './private-estom/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['joueurs'] as $bomID => $pseudo) {
        $pseudo = substr(trim($pseudo), 0, 20); // Sécurisation (max 20 caractères)
        $sql = "UPDATE BOM SET Joueur = ? WHERE ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $pseudo, $bomID);
        $stmt->execute();
    }
}

echo "Mise à jour réussie !";
