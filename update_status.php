<?php
require 'private-estom/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = $conn->real_escape_string($_POST['status']);

    // Conversion du statut en valeur numérique
    switch ($status) {
        case 'stopped':
            $statusValue = 0;
            break;
        case 'running':
            $statusValue = 1;
            break;
        case 'paused':
            $statusValue = 2;
            break;
        case 'loading':
            $statusValue = 3;
            break;
        default:
            echo "Statut invalide.";
            $conn->close();
            exit();
    }

    $updateSql = "";

    if ($_POST['status'] === 'running' && isset($_POST['temps_restant'])) {
        $temps_restant = (int)$_POST['temps_restant'];
    
        // ➤ Récupérer la durée totale
        $result = $conn->query("SELECT Duree FROM Config WHERE id = 1");
        if ($result && $row = $result->fetch_assoc()) {
            $duree = (int)$row['Duree'];
            $temps_ecoule = $duree - $temps_restant;
            $nouvelle_heure_depart = date('Y-m-d H:i:s', time() - $temps_ecoule);
    
            $stmt = $conn->prepare("UPDATE Config SET Status = 1, Heure_depart = ?, Temps_restant = NULL WHERE id = 1");
            $stmt->bind_param("s", $nouvelle_heure_depart);
            $stmt->execute();
            $stmt->close();
            echo 'Reprise réussie';
            exit;
        } else {
            echo "Erreur lors de la récupération de la durée.";
            $conn->close();
            exit;
        }
    }    
    
    if ($status === 'running') {
        // Vérifie si on a déjà une heure_depart
        $result = $conn->query("SELECT Heure_depart, Temps_restant FROM Config WHERE id = 1");
        if ($result && $row = $result->fetch_assoc()) {
            if (empty($row['Heure_depart']) || $row['Heure_depart'] === '0000-00-00 00:00:00') {
                // On redémarre à partir de Temps_restant (s'il y en a), sinon durée complète
                $now = date("Y-m-d H:i:s");
                $updateSql = "UPDATE Config SET Status = $statusValue, Heure_depart = '$now', Temps_restant = NULL WHERE id = 1";
            } else {
                // Ne pas réinitialiser heure_depart
                $updateSql = "UPDATE Config SET Status = $statusValue WHERE id = 1";
            }
        } else {
            echo "Erreur lors de la récupération des données.";
            $conn->close();
            exit();
        }

    } elseif ($status === 'paused') {
        // Calcule le temps restant
        $result = $conn->query("SELECT Heure_depart, Duree FROM Config WHERE id = 1");
        if ($result && $row = $result->fetch_assoc()) {
            $heureDepart = strtotime($row['Heure_depart']);
            $duree = (int)$row['Duree'];
            $tempsEcoule = time() - $heureDepart;
            $tempsRestant = max(0, $duree - $tempsEcoule);

            $updateSql = "UPDATE Config SET Status = $statusValue, Temps_restant = $tempsRestant, Heure_depart = NULL WHERE id = 1";
        } else {
            echo "Erreur lors de la récupération des données.";
            $conn->close();
            exit();
        }

    } elseif ($status === 'stopped') {
        // Reset total : plus d'heure de départ ni de temps restant
        $updateSql = "UPDATE Config SET Status = $statusValue, Temps_restant = NULL, Heure_depart = NULL WHERE id = 1";

    } elseif ($status === 'loading') {
        // Loading : juste MAJ du statut
        $updateSql = "UPDATE Config SET Status = $statusValue WHERE id = 1";
    }

    if (!empty($updateSql) && $conn->query($updateSql) === TRUE) {
        echo "Mise à jour réussie. Nouveau statut : $status ($statusValue)";
    } else {
        echo "Erreur SQL : " . $conn->error;
    }

    $conn->close();
}
?>
