<?php
$error = null;
$success = false;

// si aucun token de réinitialisation de mot de passe est fourni
if (!isset($_REQUEST['token'])) {
	exit(header('Location: forgotten-password.php'));
}

// vérification de l'existence du token
include('api/init.php');
$request = request_database('SELECT * FROM FORGOTREQUEST JOIN USERS ON USERS.id = FORGOTREQUEST.userId WHERE token = ', $_REQUEST['token'])->fetch_assoc();

if ($request !== null && isset($_REQUEST['password'], $_REQUEST['password2'])) {
	// changement de mot de passe sauf s'il est trop court ou si les mots de passe ne sont pas identiques
	if (strlen($_REQUEST['password']) < 4) {
		$error = 'Ton mot de passe est vraiment trop court 😵';
	} else if ($_REQUEST['password'] !== $_REQUEST['password2']) {
		$error = 'Ce ne sont pas les mêmes mots de passe 🥴';
	} else {
		$hashed_password = hash('sha512', $_POST['password']);
		request_database('UPDATE `USERS` JOIN FORGOTREQUEST ON USERS.id = FORGOTREQUEST.userId SET `password` = ', $hashed_password, ' WHERE token = ', $_REQUEST['token']);
		request_database('DELETE FROM FORGOTREQUEST WHERE token = ', $_REQUEST['token']);
		$success = true;
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Réinitialisation de mot de passe | <?= htmlspecialchars(get_site_name()) ?></title>
		<link rel="stylesheet" href="style.css" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
	</head>
	<body>
		<section class="box column">
			<?php if ($request === null) { ?>
				<h1>Cette requête n'existe pas ou a expirée 🤕</h1>
			<?php } else if (!$success) { ?>
				<form method="POST" action="?token=<?= htmlspecialchars($_REQUEST['token']) ?>">
					<h1>Réinitialisation de mot de passe</h1>
					<input name="password" type="password" placeholder="Nouveau mot de passe" required autofocus />
					<input name="password2" type="password" placeholder="Confirmation du mot de passe" required />
					<input type="submit" value="Changer de mot de passe" class="good" />
					<?php if ($error !== null) { ?>
						<p class="error"><?= $error ?></p>
					<?php } ?>
				</form>
			<?php } else { ?>
				<h1>Ton mot de passe a bien été modifié 🥳</h1>
				<a class="button large" href="login.php">🔑 Je me reconnecte</a>	
			<?php } ?>
		</section>
	</body>
</html>