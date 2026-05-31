<?php
require 'debut.inc.php';
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    header("Location: ./index.php");
    exit();
} // if
require 'head.inc.php';
require 'bodyHeader.inc.php';
?>
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
<?php
require 'fin.inc.php';
?>
