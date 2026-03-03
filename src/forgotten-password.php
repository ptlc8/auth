<?php
$admin_email_address = $_SERVER['SERVER_ADMIN'];
$error = null;
$success = false;
$mailed = false;
include('api/init.php');

if (isset($_REQUEST['username'])) {
	if (use_hcaptcha() && (!isset($_REQUEST['h-captcha-response']) || empty($_REQUEST['h-captcha-response']))) {
		$error = 'Il faut que tu complètes le captcha juste au dessus 👾';
	} else if (use_hcaptcha() && !verify_hcaptcha($_POST['h-captcha-response'])) {
		$error = 'La vérification de ton humanité à échouer, réessaye 👽';
	} else {
		// envoi de l'e-mail
		$user = request_database('SELECT email, id, name FROM USERS WHERE name = ', $_REQUEST['username'])->fetch_assoc();
		if ($user !== null) {
			$success = true;
			$email = $user['email'];
			$name = htmlspecialchars($user['name']);
			do $token = generate_token(32);
			while (request_database('SELECT * FROM FORGOTREQUEST WHERE token = ', $token)->num_rows !== 0);
			request_database('REPLACE INTO `FORGOTREQUEST` (`userId`, `token`, `expire`) VALUES (', $user['id'], ', ', $token, ', CURDATE() + INTERVAL 7 DAY)');
			$url = get_protocol().'://'.get_host().'/reset-password.php?token='.$token;
			$mail = <<<MAIL
<html>
	<body>
		<div style="text-align: center;">
			Salut $name,
			<br /> J'ai appris que tu avais perdu ton mot de passe,
			<br /> C'est pas cool
			<br /> Heureuseument je suis là
			<br /> J'ai donc concocté pour toi un petit lien :
			<br /> <a href="$url">CLIQUE-MOI !</a>
			<br /> Celui-ci te permettra de le changer
			<br /> Mais attention, il n'est valable que 7 jours
			<br /> (c'est beaucoup)
			<br /> Sur ce, passe une bonne journée.
		</div>
	</body>
</html>
MAIL;
			$mailed = mail($email, '['.get_site_name().'] Récupération de ton mot de passe', $mail, array(
				'From' => get_site_name().' <no-reply@'.get_host().'>',
				'Return-Path' => 'no-reply@'.get_host(),
				'MIME-Version' => '1.0',
				'Content-type' => 'text/html; charset=utf-8'
			));
		} else {
			$error = 'Le nom d\'utilisateur est inconnu';
		}
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Mot de passe oublié | <?= htmlspecialchars(get_site_name()) ?></title>
		<link rel="stylesheet" href="style.css" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<script src="https://www.hCaptcha.com/1/api.js" async defer></script>
	</head>
	<body>
		<section class="box column">
			<?php if (!$success) { ?>
				<form method="POST" action="">
					<h1>Mot de passe oublié</h1>
					<input name="username" type="text" placeholder="Nom d'utilisateur" required autofocus />
					<?php if(use_hcaptcha()) { ?>
					<div class="h-captcha" data-sitekey="<?= get_hcaptcha_sitekey() ?>"></div>
					<?php } ?>
					<input type="submit" value="Changer de mot de passe" class="good" />
					<?php if ($error !== null) { ?>
						<p class="error"><?= $error ?></p>
					<?php } ?>
				</form>
			<?php } else { ?>
				<?php if ($mailed) { ?>
					<h1>Un mail à été envoyé à l'adresse <?= preg_replace('/(?<=.{4}).(?=.*@)/', '*', $email) ?> 😜</h1>
					<p>Dans celui-ci tu trouveras un lien pour changer ton mot de passe.</p>
				<?php } else { ?>
					<h1>Une erreur est survenue lors de l'envoi du mail. 😵</h1>
					<p>Tu peux envoyer un mail à <?= $admin_email_address ?></p>
				<?php } ?>
			<?php } ?>
		</section>
	</body>
</html>
