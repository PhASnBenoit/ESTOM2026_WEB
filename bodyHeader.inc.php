<body>
<header>

    <h1 class="title">
        <b style="color:yellow;">E</b>space de
        <b style="color:yellow;">S</b>ensibilisation
        au <b style="color:yellow;">T</b>ri des
        <b style="color:yellow;">O</b>rdures
        <b style="color:yellow;">M</b>énagères
    <?php if (isset($_SESSION['auth']) && $_SESSION['auth'] === true): ?>
        <a class="btn logout-btn" href="./logout.php">Déconnecter</a>
    <?php endif; ?>
    </h1>

    (<?php echo "v$VERSION"; ?>)

        <div class="header-bar">
            <div class="logo" id="logo">
    <?php if (isset($_SESSION['auth']) && $_SESSION['auth'] === true): ?>
                <a href="./menu.php">
                    <img src="./img/estom-logo.png" alt="logo ESTOM">
                </a>
    <?php endif; ?>
            </div>
        </div>

    <div class="wave"></div>

</header>
