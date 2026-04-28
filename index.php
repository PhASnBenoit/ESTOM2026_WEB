<?php
// vérifié par PhA
// v2.0 du 22/04/2026
// v2.1 22/04/2026 Correction bug affichage PAV
// v2.2 22/04/2026 Correction bug podium
// v2.3 28/04/2026 Correction raz partie et Progression
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
        <div class="logo" id="logo">
            <a href="./index.php">
                <img src="./img/estom-logo.png" alt="" id="estom-logo">
            </a>
        </div>
        <div class="wave"></div>
    </header>
    <main>
        <div class="image-container">
            <div class="manetteImg">
                <a href="./gestion-de-la-partie.php">
                    <img src="./img/manette.png" alt="Manette" id="manette" class="manette-image">
                </a>
            </div>
            <div class="engrenagesImg">
                <a href="./parametre-du-jeu.php">
                    <img src="./img/engrenages.png" alt="Engrenages" id="engrenages" class="engrenages-image">
                </a>
            </div>
        </div>
        
    </main>
    <footer>
        <p>&copy; 2026 ESTOM</p>
    </footer>
</body>
</html>
