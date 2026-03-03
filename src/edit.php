<?php
$error = null;
include('api/init.php');

$user = login_from_session();
if ($user == null) {
    exit(header('Location: login.php?go='.urlencode($_SERVER['REQUEST_URI'])));
}

// modifier un utilisateur (hors id)
function update_user($id, $username, $email, $newpassword, $newpassword2, $firstname, $lastname) {
	$frags = [];
	/*if (isset($username) && strlen($username) > 0) {
    	if (request_database('SELECT * FROM USERS WHERE `name` = ', $username, ' AND `id` != ', $id)->num_rows !== 0)
			return 'Ce nom d\'utilisateur est déjà utilisé 😦';
		array_push($frags, ', name = ', $username);
	}*/
	if (isset($email) && strlen($email) > 0) {
    	if (request_database('SELECT * FROM USERS WHERE `email` = ', $email, ' AND `id` != ', $id)->num_rows !== 0)
			return 'Cette adresse e-mail est déjà utilisée 🤔';
		array_push($frags, ', email = ', $email);
	}
	if ((isset($newpassword) && strlen($newpassword) > 0) || (isset($newpassword2) && strlen($newpassword2) > 0)) {
    	if (!isset($newpassword) || strlen($newpassword) < 4)
			return 'Ton nouveau mot de passe est vraiment trop court 😵';
    	else if ($newpassword !== $newpassword2)
			return 'Tu n\'as pas écrit 2 le même nouveau mot de passe 🥴';
		array_push($frags, ', password = ', hash('sha512', $newpassword));
	}
	if (isset($firstname) && strlen($firstname) > 0) {
		array_push($frags, ', firstName = ', $firstname);
	}
	if (isset($lastname) && strlen($lastname) > 0) {
		array_push($frags, ', lastName = ', $lastname);
	}
    if (count($frags) == 0)
        return null;
	$frags[0] = preg_replace('/,/', 'UPDATE `USERS` SET', $frags[0], 1);
	array_push($frags, ' WHERE `id` = ', $id);
    print_r($frags);
	request_database(...$frags);
	return null;
}

if (isset($_REQUEST['password']) && (isset($_REQUEST['username']) || isset($_REQUEST['email']) || isset($_REQUEST['newpassword']) || isset($_REQUEST['newpassword2']) || isset($_REQUEST['firstname']) || isset($_REQUEST['lastname']))) {
    if (hash('sha512', $_REQUEST['password']) == $user['password']) {
        $error = update_user($user['id'], $_REQUEST['username'], $_REQUEST['email'], $_REQUEST['newpassword'], $_REQUEST['newpassword2'], $_REQUEST['firstname'], $_REQUEST['lastname']);
    } else {
        $error = 'Mot de passe actuel invalide. 😱';
    }
    if ($error == null)
        exit(header('Location: .'));
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Modification | <?= htmlspecialchars(get_site_name()) ?></title>
        <link rel="stylesheet" href="style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
    </head>
    <body>
        <section class="box column">
            <?php if (true) { ?>
                <form method="post" action="">
                    <h1>Modifier ton compte</h1>
                    <!--<label for="username">Nouveau nom d'utilisateur</label>
                    <input id="username" type="text" name="username" placeholder="<?= $user['name'] ?>" value="<?= htmlspecialchars($_REQUEST['username'] ?? '') ?>" />-->
                    <label for="email">Nouvelle adresse e-mail</label>
                    <input type="email" name="email" placeholder="<?= $user['email'] ?>" value="<?= htmlspecialchars($_REQUEST['email'] ?? '') ?>" autofocus />
                    <label for="newpassword">Nouveau mot de passe</label>
                    <input type="password" name="newpassword" placeholder="●●●●●●●●" />
                    <input type="password" name="newpassword2" placeholder="●●●●●●●●" />
                    <label for="firstname">Nouveaux prénom et nom</label>
                    <input type="text" name="firstname" placeholder="<?= $user['firstName'] ?>" value="<?= htmlspecialchars($_REQUEST['firstname'] ?? '') ?>" />
                    <input type="text" name="lastname" placeholder="<?= $user['lastName'] ?>" value="<?= htmlspecialchars($_REQUEST['lastname'] ?? '') ?>" />
                    <label for="password">Mot de passe actuel (nécessaire)</label>
                    <input type="password" name="password" required />
                    <input type="submit" class="good" value="Enregistrer" />
                    <?php if ($error !== null) { ?>
                        <p class="error"><?= $error ?></p>
                    <?php } else { ?>
                        <p class="helper">
                            Ton image de profil est générée en fonction de ton adresse e-mail.
                            <br />
                            Ou sinon tu peux avoir celle de ton choix via <a href="https://gravatar.com/profile/avatars" target="_blank">ton profil Gravatar</a>.
                        </p>
                    <?php } ?>
                </form>
            <?php } ?>
            <a class="button bad" href="delete.php">🗑️ Supprimer mon compte</a>
            <a class="button" href=".">👤 Retour au compte</a>
        </section>
    </body>
</html>