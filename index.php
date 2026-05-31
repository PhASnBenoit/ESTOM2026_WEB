<?php
require 'debut.inc.php';
require 'head.inc.php';
require 'bodyHeader.inc.php';
?>

<section>

    <h2>Authentification</h2>
    <p>Veuillez entrer le code d'accès</p>

    <?php
    if (isset($_GET['error'])) {
        echo "<p style='color:red; font-weight:bold;'>❌ Mot de passe incorrect</p>";
    }
    ?>

    <form method="POST" action="checkPassword.inc.php">

        <fieldset>
            <legend>Code PIN</legend>

            <label for="password">Mot de passe (5 chiffres)</label><br><br>

            <input
                type="password"
                name="password"
                id="password"
                pattern="[0-9]{5}"
                maxlength="5"
                required
                inputmode="numeric"
                placeholder="•••••"
                style="
                    text-align:center;
                    font-size: 1.5rem;
                    letter-spacing: 10px;
                    width: 200px;
                "
            >
        </fieldset>

        <br>

        <button type="submit" class="btn">Valider</button>

    </form>

</section>

<?php
require 'fin.inc.php';
?>
