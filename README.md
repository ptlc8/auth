# Auth server

Serveur d'authentification pour les applications Ambi.dev.

Actuellement déployé sur [ambi.dev](https://auth.ambi.dev).


## Lancer en local

### Avec Docker

 - cloner le projet avec git
 - optionnel : mettre les variables d'environnement `HCAPTCHA_SECRET`, `HCAPTCHA_SITEKEY`, `MAIL_HOST` et `SITE_NAME` dans un fichier `.env` à la racine du projet
 - lancer les conteneurs docker avec `docker compose up -d`


### Sans Docker

Il est possible de lancer le projet en local.
Pour cela il faut faudra PHP et MySQL.
 - cloner le projet avec git
 - créer un utilisateur et une base de données MySQL
 - créer un fichier src/api/credentials.php contenant identifiants de la base de données, sous cette forme :
```php
<?php
define('DB_HOST', 'my_host');
define('DB_USER', 'my_user');
define('DB_PASS', 'my_password');
define('DB_NAME', 'my_dbname');
// les lignes suivantes sont optionnelles
define('SITE_NAME', 'Ambi.dev');
define('HCAPTCHA_SECRET', '0x123456789aBcDeF');
define('HCAPTCHA_SITEKEY', '0123abcd-45ef-6789-abcd-ef0123456789');
?>
```
 - exécuter dans la base de données le script SQL [database/init.sql](database/init.sql)
 - optionnel mais nécessaire pour envoyer les mails de récupération, installer un serveur SMTP local (port 25)
 - lancer le serveur php


## Dépendances

- [PHP 8](https://www.php.net/)
  - [MySQLi](https://www.php.net/manual/en/book.mysqli.php)
- [MySQL 8](https://www.mysql.com/) ou [MariaDB 10](https://mariadb.org/)