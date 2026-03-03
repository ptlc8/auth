<?php
$close = false;
$back = false;

include('api/init.php');
logout();

if (isset($_REQUEST['closeafter']))
    $close = true;
else if (isset($_REQUEST['back']))
    $back = true;
else
    exit(header('Location: .'));
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Désauthentification | <?= htmlspecialchars(get_site_name()) ?></title>
        <link rel="stylesheet" href="style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
    </head>
    <body>
        <section class="box column">
            <h1>Désauthentification réussie</h1>
            <p>Tu as bien été désauthentifié de ton compte. 😶‍🌫️</p>
            <?php if ($close) { ?>
                <script>window.close();</script>
                <p>Tu peux fermer cet onglet.</p>
            <?php } else if ($back) { ?>
                <script>window.history.go(-1);</script>
            <?php } ?>
            <a class="button large" href=".">🏠 Retour à l'accueil</a>
        </section>
    </body>
</html>