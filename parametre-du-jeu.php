<?php
// Vérifiée par PhA
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
            <h2>Paramétrage du Jeu</h2>
            
            <?php
            // Inclusion du fichier de connexion
//            include '/var/www/html/private-estom/db_connect.php';
            require 'private-estom/db_connect.php';

            // Initialisation des paramètres
            $tempsPartie = 180;
            $options = 1;
            $ptsRecolte = 10;
            $nbrPAV = 10;
            $malusCollision = 2;
            $nbrBOM_V = 1;
            $nbrBOM_J = 1;
            $nbrBOM_N = 1;
            $nbrBOM_B = 1;
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

            // Si le formulaire est soumis
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Mise à jour du temps de la partie
                // Validation des entrées utilisateur avec double vérification des valeurs!
                $nouveauTempsPartie = filter_var($_POST['temps_partie'], FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 30, 'max_range' => 600]
                ]) ?? $tempsPartie;

                $nouveauNbrPAV = filter_var($_POST['nombre_PAV'], FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1, 'max_range' => 10]
                ]) ?? $nbrPAV;
                $nouveauMalusCollision = filter_var($_POST['malus_collision'], FILTER_VALIDATE_FLOAT);
                if ($nouveauMalusCollision === false || $nouveauMalusCollision < 0.2 || $nouveauMalusCollision > 5) {
                    $nouveauMalusCollision = $malusCollision;
                }                
                

                $nouveauNbrBOM_V = filter_var($_POST['nombre_bom_v'], FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1, 'max_range' => 2]
                ]) ?? $nbrBOM_V;
                $nouveauNbrBOM_J = filter_var($_POST['nombre_bom_j'], FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1, 'max_range' => 2]
                ]) ?? $nbrBOM_J;
                $nouveauNbrBOM_N = filter_var($_POST['nombre_bom_n'], FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1, 'max_range' => 2]
                ]) ?? $nbrBOM_N;
                $nouveauNbrBOM_B = filter_var($_POST['nombre_bom_b'], FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1, 'max_range' => 2]
                ]) ?? $nbrBOM_B;

                $nouveauPtsRecolte = filter_var($_POST['pts_recolte'], FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1, 'max_range' => 100]
                ]) ?? $ptsRecolte;

                //Option, si Option = 0 alors Option A, si Option = 1 Alors option B !
                if (isset($_POST['regle']) && $_POST['regle'] === 'option_a') {
                    $nouveauOptions = 0;
                } else {
                    $nouveauOptions = 1;
                }

                // Mise à jour dans la base de données
                $sqlUpdate = "UPDATE Config SET Options = ?, PtsRecolte = ?, NbrPAV = ?, MalusCollision = ?, NbrBOM_V = ?, NbrBOM_J = ?, NbrBOM_N = ?, NbrBOM_B = ?, duree = ? WHERE Id = 1";
                $stmt = $conn->prepare($sqlUpdate);
                $stmt->bind_param("iiidiiiii", $nouveauOptions, $nouveauPtsRecolte, $nouveauNbrPAV, $nouveauMalusCollision,                  $nouveauNbrBOM_V, $nouveauNbrBOM_J, $nouveauNbrBOM_N, $nouveauNbrBOM_B, $nouveauTempsPartie);
                $stmt->execute();
                
                header("Location: parametre-du-jeu.php?success=1");
                exit;
            }

            // Fermeture de la connexion
            $conn->close();
            ?>

            <div id="Principale">
                <form method="post" action="parametre-du-jeu.php" id="BoiteRegles">

                    <fieldset id="fieldset">
                        <legend id="titre">Règles du Jeu</legend>

                        <div class="Regle-Container">

                            <label for="temps_partie" id="temps_partie">Temps de la partie (en secondes) :</label>
                            <input type="number" id="temps_partie_input" name="temps_partie" value="<?php echo htmlspecialchars($tempsPartie); ?>" step="10" min="30" max="600" required>

                            <label for="nombre_PAV" id="nombre_PAV">Nombre de PAV par couleur :</label>
                            <input type="number" id="nombre_PAV_input" name="nombre_PAV" value="<?php echo htmlspecialchars($nbrPAV); ?>" min="1" max="10" required>

                            <label for="malus_collision" id="malus_collision">Malus de collision :</label>
                            <input type="number" id="malus_collision_input" name="malus_collision" value="<?php echo htmlspecialchars($malusCollision); ?>" step="0.2" min="0.2" max="5" required>

                            <label id="optionA_label" for="optionA">
                                <input type="radio" name="regle" id="optionA" value="option_a" <?php echo $options == 0 ? 'checked' : ''; ?>>
                                <span id="optionA_text">Option A : Points identiques pour chaque récolte</span>
                            </label>
                            <div id="optionA_settings">
                                <label for="pts_recolte">Points par PAV récolté :</label>
                                <input type="number" id="pts_recolte_input" name="pts_recolte" value="<?php echo htmlspecialchars($ptsRecolte); ?>" min="1" max="100">
                            </div>

                            <label  id="optionB_label" for="optionB">
                                <input type="radio" name="regle" id="optionB" value="option_b" <?php echo $options == 1 ? 'checked' : ''; ?>>
                                <span id="optionB_text">Option B : Points progressifs selon l’avancement de la récolte</span>
                            </label>

                            <div id="tableau_Nombres">
                                <label for="nombre_bom_j" id="nombre_jaune">BOM Jaune:</label>
                                <input type="number" id="nombre_jaune_input" name="nombre_bom_j" value="<?php echo htmlspecialchars($nbrBOM_J); ?>" min="0" max="2" required>

                                <label for="nombre_bom_v" id="nombre_vert">BOM Verte:</label>
                                <input type="number" id="nombre_vert_input" name="nombre_bom_v" value="<?php echo htmlspecialchars($nbrBOM_V); ?>" min="0" max="2" required>

                                <label for="nombre_bom_b" id="nombre_bleu">BOM Bleu:</label>
                                <input type="number" id="nombre_bleu_input" name="nombre_bom_b" value="<?php echo htmlspecialchars($nbrBOM_B); ?>" min="0" max="2" required>

                                <label for="nombre_bom_n" id="nombre_noir">BOM Noir:</label>
                                <input type="number" id="nombre_noir_input" name="nombre_bom_n" value="<?php echo htmlspecialchars($nbrBOM_N); ?>" min="0" max="2" required>
                            </div>
                        </div>
                    </fieldset>

                    <button type="submit">Sauvegarder les paramètres</button>
                </form>

                <div id="IconesParametre">
                    <?php 
                    $couleurs = [
                        "Jaune" => $nbrBOM_J,
                        "Vert" => $nbrBOM_V,
                        "Bleu" => $nbrBOM_B,
                        "Noir" => $nbrBOM_N
                    ];
                    $maxBOM = max($nbrBOM_V, $nbrBOM_J, $nbrBOM_N, $nbrBOM_B);
                    $position = 1; // Position de chaque colonne dans la grille

                    foreach ($couleurs as $couleur => $nombre) {
                        echo '<div id="ColonneBOM' . $couleur . '" style="grid-column: ' . $position . '; grid-row: 1;">';
                        
                        // Afficher 1 ou 2 BOM par couleur
                        for ($i = 0; $i < $maxBOM; $i++) {
                            echo '<div id="BOM-' . $couleur . '-' . $i . '" class="bom-container">
                                    <img src="./img/BOM-' . $couleur . '.png" alt="Camion ' . $couleur . '" id="BOM-' . $couleur . '-' . $i . '" class="grise" width="75" height="45">
                                    <span id="BOM-IP-' . $couleur . '-' . $i . '" class="ip-text"></span>
                                  </div>';
                        }
                        echo '</div>';
                        echo '<div id="ColonnePAV' . $couleur . '" style="grid-column: ' . $position . '; grid-row: 2;">';
                        for ($i = 0; $i < 10 ; $i++) { 
                            echo '<div class="LignePAV">
                                    <img src="./img/PAV-' . $couleur . '.png" alt="PAV ' . $couleur . '" id="PAV-' . $couleur . '-' . $i . '" class="grise imagePAV" width="29" height="45">
                                    <span id="PAV-IP-' . $couleur . '-' . $i . '" class="ip-text"></span>
                                </div>';
                        } // for
                        echo '</div>';
                        $position++; // Passe à la colonne suivante
                    } // for
                    ?>
                </div>

            </div>
        </section>
    </main>
    <footer>
        <p>&copy; 2026 ESTOM</p>
    </footer>
</body>
    <script src="js/parametre-du-jeu/updateConnexion.js"></script>
    <script>
        // Fonction pour gérer la visibilité du champ "Pts par PAV"
        function togglePointsField() {
            const optionAInput = document.querySelector('input[name="regle"][value="option_a"]');
            const pointsField = document.getElementById("optionA_settings");

            // Afficher ou masquer le champ selon l'option sélectionnée
            if (optionAInput.checked) {
                pointsField.style.display = "block";
            } else {
                pointsField.style.display = "none";
            }
        }

        // Ajout d'événement
        document.addEventListener("DOMContentLoaded", () => {
            // Exécuter une première fois pour régler l'affichage initial
            togglePointsField();

            // Ajouter des "écouteurs" sur les options de règle
            document.querySelectorAll('input[name="regle"]').forEach(input => {
                input.addEventListener("change", togglePointsField);
            });
        });
    </script>
</html>
