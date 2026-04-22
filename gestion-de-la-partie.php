<?php
// vérifié par PhA
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESTOM</title>
    <link rel="stylesheet" href="./styles.css">
    <link rel="icon" type="image/x-icon" href="./img/estom-logo.ico">
</head>
<body>
    <header>
        <h1 class="title">ESTOM</h1>
        <nav class="navbar">
            <ul class="menu">
            </ul>
        </nav>
        <div class="logo" id="logo">
            <a href="./index.php">
                <img src="./img/estom-logo.png" alt="" id="estom-logo">
            </a>
        </div>
    </header>
    <main>
        <section>
            <h2>Gestion de la partie</h2>
            <div id="timer" class="timer">Cliquez sur le bouton pour lancer la Partie !</div>

            <div id="TableauGestion" class="GTableauGestion">
                <!-- Bouton pour ouvrir la fenêtre de saisie des joueurs -->
                <button id="openModalButton">Ajouter Joueurs</button>

                <!-- Fenêtre modale Joueur -->
                <div id="playerModal" class="modal">
                    <div class="modal-content">
                        <span class="close-btn">&times;</span>
                        <h2>Ajoutez vos joueurs</h2>
                        <form id="playerForm">
                            <div id="playerInputs">
                                <?php
                                require './private-estom/db_connect.php';

                                // Récupération du nombre de BOM configurées
                                $sql = "SELECT NbrBOM_V, NbrBOM_J, NbrBOM_N, NbrBOM_B FROM Config WHERE Id = 1";
                                $result = $conn->query($sql);
                                $nbrBOMConfig = 0;

                                if ($result && $result->num_rows > 0) {
                                    $row = $result->fetch_assoc();
                                    $nbrBOMConfig = $row['NbrBOM_V'] + $row['NbrBOM_J'] + $row['NbrBOM_N'] + $row['NbrBOM_B'];
                                }

                                // Récupération du nombre de BOM enregistrées
                                $sql = "SELECT COUNT(*) AS totalBOM FROM BOM";
                                $result = $conn->query($sql);
                                $nbrBOMEnregistrees = 0;

                                if ($result && $result->num_rows > 0) {
                                    $row = $result->fetch_assoc();
                                    $nbrBOMEnregistrees = $row['totalBOM'];
                                }
                                $colorMap = [
                                    0=> "Jaune",
                                    1=> "Vert",
                                    2=> "Bleu",
                                    3=> "Noir"
                                ];

                                // Récupération des BOM avec leur ID, couleur et joueur
                                $sql = "SELECT ID, Couleur, Joueur FROM BOM ORDER BY Couleur, ID";
                                $result = $conn->query($sql);

                                // Conversion explicite en entiers
                                $nbrBOMConfig2 = (int) $nbrBOMConfig;
                                $nbrBOMEnregistrees2 = (int) $nbrBOMEnregistrees;

                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $bomID = $row['ID'];
                                        $couleur = $colorMap[$row['Couleur']];
                                        $joueur = $row['Joueur'] ?? "";

                                        echo "<div class='player-input'>
                                                <span>BOM#$bomID - $couleur</span>
                                                <input type='text' name='joueurs[$bomID]' value='$joueur' maxlength='20' placeholder='Pseudo joueur' required>
                                            </div>";
                                    }
                                }
                                // Affichage du message d'alerte si le nombre de BOM est incorrect
                                if ($nbrBOMConfig2 !== $nbrBOMEnregistrees2) {
                                    echo "<p style='color: red; font-weight: bold; margin-top: 10px;'>
                                            ⚠ Attention : La configuration prévoit <strong>$nbrBOMConfig2 BOM</strong>, 
                                            mais <strong>$nbrBOMEnregistrees2 BOM</strong> sont enregistrées !
                                        </p>";
                                }
                                ?>
                            </div>
                            <button type="submit">Valider</button>
                        </form>
                    </div>
                </div>

                <?php
                // Inclusion du fichier de connexion
                require './private-estom/db_connect.php';

                // Récupérer la durée du timer (en secondes) depuis la base de données
                $timerDuration = 180; // Valeur par défaut : 3 minutes (180 secondes)
                $sql = "SELECT duree FROM Config WHERE id = 1"; // Exemple de table de config
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $timerDuration = (int)$row['duree'];
                }

                // Injecter la durée du timer dans le script JS
                echo "<script>const timerDuration = $timerDuration;</script>";
                
                // Récupération des paramètres existants
                $sql = "SELECT Options, PtsRecolte, NbrPAV, MalusCollision, NbrBOM_V, NbrBOM_J, NbrBOM_N, NbrBOM_B, duree FROM Config WHERE Id = 1";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $options = $row['Options'];
                    $ptsRecolte = $row['PtsRecolte'];
                    $nbrPAV = $row['NbrPAV'];
                    $malusCollision = $row['MalusCollision'];
                    $nbrBOM_V = $row['NbrBOM_V'];
                    $nbrBOM_J = $row['NbrBOM_J'];
                    $nbrBOM_N = $row['NbrBOM_N'];
                    $nbrBOM_B = $row['NbrBOM_B'];
                    $tempsPartie = $row['duree'];
                }
                echo "<script>
                            const malusCollision = $malusCollision;
                            const ptsRecolte = $ptsRecolte;
                            const optionMode = $options;
                            const nbrPAV = $nbrPAV;
                        </script>";

                $colorMap = [
                    "Jaune" => 0,
                    "Vert" => 1,
                    "Bleu" => 2,
                    "Noir" => 3
                ];

                ?>
                
                <button id="startGameButton">Lancer la partie</button>
                <button id="resetGameButton" style="display:none;">Réinitialiser la partie</button>
            </div> <!-- fin div "TableauGestion" -->
            
            <!-- Modal "podiumModal" -->
            <div id="podiumModal" class="modal" style="display:none;">
                <div class="modal-content">
                    <div class="img">
                        <img src="img/podium.png" alt="Podium" style="width:100%;">
                    </div>
                    <div id="classementContent">
                        <!-- Contenu du podium injecté ici par JavaScript -->
                    </div>
                </div>
            </div>


            <div id="tableauBars">
                <?php
//    CRGB(255,205,0); // jaune RAL1018
//    CRGB(0,124,89);  // vert RAL6032  
//    CRGB(0,83,135);  // Bleu RAL5015  
//    CRGB(85,93,80);  // Gris RAL7011 
                $couleurs = [
                    "Jaune" => $nbrBOM_J,
                    "Vert" => $nbrBOM_V,
                    "Bleu" => $nbrBOM_B,
                    "Noir" => $nbrBOM_N
                ];
                $rgbaColors = [ //rgb correspondant aux couleurs BOM & PAV
                /*    "Vert" => "rgba(14, 202, 26, 0.6)",
                    "Jaune" => "rgba(250, 206, 0, 0.6)",
                    "Noir" => "rgba(59, 59, 59, 0.6)",
                    "Bleu" => "rgba(23, 140, 221, 0.6)"*/
                    "Jaune" => "rgba(255, 205, 0, 0.6)",
                    "Vert" => "rgba(0, 124, 89, 0.6)",
                    "Bleu" => "rgba(0, 83, 135, 0.6)",
                    "Noir" => "rgba(85, 93, 80, 0.6)"
                ];

                foreach ($couleurs as $couleur => $nombreBOM) {
                    // Récupérer les IP des BOM de cette couleur
                    $sql = "SELECT IPAddr FROM BOM WHERE Couleur = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $colorMap[$couleur]);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    $ipList = [];
                    while ($row = $result->fetch_assoc()) {
                        $ipList[] = $row['IPAddr'];
                    }
                    $stmt->close();

                    for ($i = 0; $i < $nombreBOM; $i++) {
                        $ip = $ipList[$i] ?? "NULL"; // Assigne une IP si disponible, sinon NULL

                        echo "<div class='route-container' data-couleur='$couleur' data-ip='$ip'>";

                        // Scoreboard à gauche du parcours
                        echo "<div class='scoreboard-container'>
                        <img src='./img/CadreScore-$couleur.png' class='scoreboard' data-couleur='$couleur' data-ip='$ip'>
                        <span class='score-text' id='score-$ip'>0</span>
                        </div>";

                        echo "<img src='./img/route.png' alt='Route' class='imageRoute'>";

                        // BOM avec IP ou sans IP
                        echo "<img src='./img/BOM-$couleur.png' alt='Camion $couleur' 
                            class='imageCamion' data-couleur='$couleur' data-ip='$ip'
                            style='position: absolute; left: 0%; top: 50%; width: 60px; opacity: 0.9; z-index: 3;'>";
                            echo "<span class='pseudo' data-ip='$ip' 
                            style='
                                display:none;
                                background-color: {$rgbaColors[$couleur]};
                            '></span>";

                        echo "<img src='./img/LigneArrive.gif' alt='Ligne d’arrivée' class='imageArrive'>";

                        for ($j = 0; $j < $nbrPAV; $j++) {
                            $espacement = 100 / ($nbrPAV + 1);
                            $positionLeft = ($j + 1) * $espacement;

                            echo "<img src='./img/PAV-$couleur.png' alt='PAV $couleur' 
                                class='imagePAV' data-couleur='$couleur' data-ip='$ip' data-index='$j'
                                style='
                                    position: absolute;
                                    top: 50%;
                                    left: {$positionLeft}%;
                                    transform: translate(-50%, -50%);
                                    width: 20px;
                                    opacity: 1;
                                    z-index: 4;
                                '>";
                        } // for j
                        echo "</div>";
                    } // for i
                } // foreach couleur
                ?>
            </div>
        </section>
    </main>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const modal = document.getElementById("playerModal");
            const openModalButton = document.getElementById("openModalButton");
            const closeModalButton = document.querySelector(".close-btn");
            const playerForm = document.getElementById("playerForm");

            openModalButton.addEventListener("click", function () {
                modal.style.display = "flex";
            });

            closeModalButton.addEventListener("click", function () {
                modal.style.display = "none";
            });

            playerForm.addEventListener("submit", function (event) {
                event.preventDefault();

                const formData = new FormData(playerForm);
                fetch('update_joueurs.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(() => {
                    modal.style.display = "none";
                    location.reload();
                })
                .catch(error => console.error("Erreur :", error));
            });
        });
    </script>
    <script>
        function showPodium() {
            loadPodium(); // exécute la fonction loadPodium du script loadPodium.js
            document.getElementById("podiumModal").style.display = "flex";
        }
        function hidePodium() {
            document.getElementById("podiumModal").style.display = "none";
        }
        // Fermeture quand on clique en dehors de la zone modale (hors .modal-content)
        document.getElementById("podiumModal").addEventListener("click", function () {
            hidePodium();
        });

        // Fermeture avec la touche Échap
        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                hidePodium();
            }
        });
    </script>
    <script src="js/gestion-de-la-partie/loadPodium.js"></script>
    <script src="js/gestion-de-la-partie/updateCamion.js"></script>
    <script src="js/gestion-de-la-partie/updateTimer.js"></script>
    <footer>
        <p>&copy; 2026 ESTOM</p>
    </footer>
</body>
</html>
