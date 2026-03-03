<?php
$valid_request = false;
$close = false;
$back = false;

include('api/init.php');
if (isset($_REQUEST['app'])) {
	$valid_request = true;
	$app = request_database('SELECT * FROM APPS WHERE id = ', $_REQUEST['app'])->fetch_assoc();
	if ($app !== null) {
		$user = login_from_session();
		if ($user === null)
			exit(header('Location: login.php?go='.urlencode($_SERVER['REQUEST_URI'])));
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			request_database('DELETE FROM `TOKENS` WHERE `user` = ', $user['id'], ' AND app = ', $_REQUEST['app']);
			/*if (isset($_REQUEST['go']))
				exit(header('Location: '.$_REQUEST['go']));*/
			if (isset($_REQUEST['closeafter']))
				$close = true;
			else if (isset($_REQUEST['back']))
				$back = true;
			$disconnected = true;
		} else {
			$disconnected = false;
		}
	}
}
?><!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Déconnexion | <?= htmlspecialchars(get_site_name()) ?></title>
		<link rel="stylesheet" href="style.css" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
	</head>
	<body>
		<?php if (isset($app)) { ?>
			<div class="custom-background" style="background-image: url('<?= addslashes($app['background']) ?>');"></div>
		<?php } ?>
		<section class="box column">
			<?php if (!$valid_request) { ?>
				<h1>Mmh... Cette page a été mal appelée 🤯</h1>
				<a class="button" href="javascript:history.back()">🔙 Revenir en arrière</a>
				<a class="button" href=".">🏠 Retouner à la page d'accueil </a>
			<?php } else if ($app === null) { ?>
				<h1>Euh... Cette application n'existe pas 🤓</h1>
				<a class="button" href="javascript:history.back()">🔙 Revenir en arrière</a>
				<a class="button" href=".">🏠 Retouner à la page d'accueil</a>
			<?php } else if (!$disconnected) { ?>
				<form method="post" action="">
					<h1>Déconnecter de <u><?= htmlspecialchars($app['name']) ?></u></h1>
					<div class="connection">
						<?= htmlspecialchars($user['name']) ?>
						<img width="50" src="avatar.php">
						❌
						<img width="50" src="<?= $app['icon'] ?? '' ?>" />
						<?= htmlspecialchars($app['name']) ?>
					</div>
					<p class="error"><u><?= htmlspecialchars($app['name']) ?></u> n'aura plus accès à :</p>
					<ul>
						<li>ton nom d'utilisateur et ton id</li>
						<!--<li>ton adresse e-mail</li>-->
					</ul>
					<input type="submit" value="Déconnecter" class="bad" />
				</form>
			<?php } else { ?>
				<h1><u><?= htmlspecialchars($app['name']) ?></u> déconnecté</h1>
				<p class="helper">Cette application a été déconnecté de ton compte. 😢</p>
				<?php if ($close) { ?>
					<script>window.close();</script>
				<?php } else if ($back) { ?>
					<script>window.history.go(-2);</script>
				<?php } ?>
			<?php } ?>
		</section>
	</body>
</html>
