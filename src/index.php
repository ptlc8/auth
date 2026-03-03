<?php
include('api/init.php');
$user = login_from_session();
if ($user == null) {
    exit(header('Location: login.php?go='.urlencode($_SERVER['REQUEST_URI'])));
}
$tokens = request_database('SELECT * FROM `TOKENS` JOIN `APPS` ON app = id WHERE `user` = '.$user['id'])->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Compte | <?= htmlspecialchars(get_site_name()) ?></title>
        <link rel="stylesheet" href="style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <script>
            window.addEventListener('load', () => {
                document.querySelectorAll('time').forEach(el => {
                    const utc = el.getAttribute('datetime');
                    if (!utc) return;
                    const date = new Date(utc);
                    el.textContent = date.toLocaleString();
                });
            });
        </script>
    </head>
    <body>
        <section class="box column">
            <h1>Ton compte</h1>
            <div class="row">
                <img width="200" height="200" class="avatar" src="avatar.php" alt="Gravatar" />
                <div class="column">
                    <span>Identifiant : <?= $user['id'] ?></span>
                    <span>Nom d'utilisateur : <?= htmlspecialchars($user['name']) ?></span>
                    <span>Adresse mail : <?= htmlspecialchars($user['email']) ?></span>
                    <span>Prénom : <?= htmlspecialchars($user['firstName']) ?></span>
                    <span>Nom : <?= htmlspecialchars($user['lastName']) ?></span>
                    <span>
                        Date d'inscription :
                        <time datetime="<?= sql_date_to_iso_date($user['createdAt']) ?>">
                            <?= sql_date_to_french_date($user['createdAt']) ?>
                        </time>
                    </span>
                    <span>
                        Dernière modification :
                        <time datetime="<?= sql_date_to_iso_date($user['updatedAt']) ?>">
                            <?= sql_date_to_french_date($user['updatedAt']) ?>
                        </time>
                    </span>
                </div>
            </div>
            <h2>Applications connectées</h2>
            <div class="column">
                <?php if (count($tokens) == 0) { ?>
                    Aucune application connectée
                <?php } else foreach ($tokens as $token) { ?>
                    <article class="box column">
                        <h3>
                            <img height="20" src="<?= $token['icon'] ?>" />
                            <?= $token['name'] ?>
                        </h3>
                        <span>
                            depuis :
                            <time datetime="<?= sql_date_to_iso_date($token['date']) ?>">
                                <?= sql_date_to_french_date($token['date']) ?>
                            </time>
                        </span>
                        <div class="row">
                            <a class="button" href="<?= $token['url'] ?>">Voir</a>
                            <a class="button bad" href="disconnect.php?back&app=<?= $token['app'] ?>">Déconnecter</a>
                        </div>
                    </article>
                <?php } ?>
            </div>
            <a class="button" href="edit.php">✏️ Modifier mon compte</a>
            <a class="button bad" href="logout.php">🚪 Se désauthentifier</a>
        </section>
    </body>
</html>