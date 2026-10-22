<?php
// si le fichier credentials.php existe, on l'inclut
@include('credentials.php');

// obtenir une variable de configuration
function get_config($name) {
	if (defined($name) && !empty(constant($name)))
		return constant($name);
	return getenv($name) ?? null;
}

// obtenir le nom de domaine
function get_host() {
	return $_SERVER['HTTP_HOST'];
}

// obtenir le protocole
function get_protocol() {
	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
		return 'https';
	if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
		return 'https';
	return 'http';
}

// obtenir le nom du site
function get_site_name() {
	return get_config('SITE_NAME') ?? get_host();
}

// obtenir une connexion à la base de données
function get_database() {
	global $mysqli;
	if (!isset($mysqli)) {
		$mysqli = new mysqli(get_config('DB_HOST'), get_config('DB_USER'), get_config('DB_PASS'), get_config('DB_NAME'));
		if ($mysqli->connect_errno) {
			exit('Erreur de connexion côté serveur, veuillez réessayer plus tard');
		}
	}
	return $mysqli;
}

function escape_database_value($value) {
	$mysqli = get_database();
	if ($value === NULL)
		return 'NULL';
	return $mysqli->real_escape_string($value);
}

// faire une requête à la base de données
function request_database(...$request_frags) {
	$request = '';
	$var = false;
	foreach ($request_frags as $frag) {
		$request .= ($var ? "'".escape_database_value($frag)."'" : $frag);
		$var = !$var;
	}
	$mysqli = get_database();
	if (!$result = $mysqli->query($request)) {
		echo 'Erreur de requête côté serveur, veuillez réessayer plus tard';
		if (str_ends_with(get_host(), 'localhost'))
			echo ' : '.$request;
		exit;
	}
	return $result;
}

// démarrer la session avec des cookies sécurisés
function start_session() {
	if (session_status() === PHP_SESSION_ACTIVE)
		return;
	session_set_cookie_params([
		'httponly' => true,
		'secure' => get_protocol() === 'https',
		'samesite' => 'Lax'
	]);
	session_start();
}

// hacher un mot de passe
function hash_password($password) {
	return password_hash($password, PASSWORD_DEFAULT);
}

// vérifier un mot de passe (accepte aussi les anciens hashs SHA-512)
function verify_password($password, $hash) {
	return password_verify($password, $hash) || hash_equals($hash, hash('sha512', $password));
}

// enregistrer l'utilisateur dans la session
function set_session_user($username, $hashed_password) {
	start_session();
	session_regenerate_id(true);
	$_SESSION['username'] = $username;
	$_SESSION['password'] = $hashed_password;
}

// connexion à un compte avec la session
function login_from_session() {
	start_session();
	if (!isset($_SESSION['username'], $_SESSION['password']))
		return null;
	return request_database('SELECT * FROM USERS WHERE `name` = ', $_SESSION['username'], ' and `password` = ', $_SESSION['password'])->fetch_assoc();
}

// connexion à un compte avec un nom d'utilisateur et un mot de passe
function login($username, $password) {
	$user = request_database('SELECT * FROM USERS WHERE `name` = ', $username)->fetch_assoc();
	if ($user === null || !verify_password($password, $user['password']))
		return null;
	if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
		$user['password'] = hash_password($password);
		request_database('UPDATE USERS SET `password` = ', $user['password'], ' WHERE `id` = ', $user['id']);
	}
	set_session_user($user['name'], $user['password']);
	return $user;
}

// révoquer tous les tokens d'applications d'un utilisateur
function revoke_tokens($user_id) {
	request_database('DELETE FROM TOKENS WHERE `user` = ', $user_id);
}

// obtenir le token CSRF de la session
function csrf_token() {
	start_session();
	if (empty($_SESSION['csrf']))
		$_SESSION['csrf'] = generate_token(32);
	return $_SESSION['csrf'];
}

// vérifier le token CSRF envoyé avec le formulaire
function check_csrf() {
	return isset($_POST['csrf']) && hash_equals(csrf_token(), $_POST['csrf']);
}

// n'autoriser que les redirections vers ce site
function safe_redirect_target($url) {
	if (is_string($url) && preg_match('#^/([^/\\\\]|$)#', $url))
		return $url;
	return '.';
}

// générer un token
function generate_token($length=32) {
	$token = '';
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-';
	for ($i = 0; $i < $length; $i++)
		$token .= $chars[random_int(0, strlen($chars) - 1)];
	return $token;
}

// déconnexion
function logout() {
	start_session();
	unset($_SESSION['username'], $_SESSION['password']);
}

// vérifier si hCaptcha est activé
function use_hcaptcha() {
	return get_config('HCAPTCHA_SECRET') && get_config('HCAPTCHA_SITEKEY');
}

// obtenir la clé du site hCaptcha
function get_hcaptcha_sitekey() {
	return get_config('HCAPTCHA_SITEKEY');
}

// vérifier la réponse hCaptcha
function verify_hcaptcha($response) {
	$hcaptcha_secret = get_config('HCAPTCHA_SECRET');
	$hcaptcha_response = file_get_contents('https://hcaptcha.com/siteverify?secret='.$hcaptcha_secret.'&response='.rawurlencode($response).'&remoteip='.$_SERVER['REMOTE_ADDR']);
	return json_decode($hcaptcha_response)->success;
}

// convertir une date SQL UTC en format ISO 8601
function sql_date_to_iso_date($sql_date) {
	if (!$sql_date)
		return null;
	$timestamp = strtotime($sql_date);
	if ($timestamp === false)
		return null;
	return date('Y-m-d\TH:i:s\Z', $timestamp);
}

// convertir une date SQL UTC en français
function sql_date_to_french_date($sql_date) {
	if (!$sql_date)
		return 'inconnue';
	$timestamp = strtotime($sql_date);
	if ($timestamp === false)
		return 'inconnue';
	return date('d/m/Y H:i', $timestamp);
}
?>