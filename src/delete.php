<?php
$deleted = false;
include('api/init.php');
$user = login_from_session();
if ($user === null)
	exit(header('Location: login.php?go='.urlencode($_SERVER['REQUEST_URI'])));
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	request_database('DELETE FROM `USERS` WHERE `id` = ', $user['id']);
	$deleted = true;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Suppression | <?= htmlspecialchars(get_site_name()) ?></title>
		<link rel="stylesheet" href="style.css" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
	</head>
	<body>
		<section class="floating container">
			<?php if ($deleted) { ?>
				<h1>Compte supprimé</h1>
				<p class="helper">Ton compte a bien été supprimé. 😫</p>
				<a class="button" href=".">🏠 Retouner à la page d'accueil</a>
			<?php } else { ?>
				<form method="post" action="">
					<h1>Supprimer ton compte</h1>
					<p class="error">⚠ Cette action est irréversible</p>
					<input type="submit" value="Supprimer mon compte" class="bad" />
				</form>
            	<a class="button" href=".">👤 Retour au compte</a>
			<?php } ?>
		</section>
	</body>
</html>
