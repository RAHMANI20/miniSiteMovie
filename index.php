<?php

/*
 * On indique que les chemins des fichiers qu'on inclut
 * seront relatifs au répertoire src.
 */
set_include_path("./src");

/* Inclusion des classes utilisées dans ce fichier */
require_once("Router.php");
require_once("model/MovieStorageMySQL.php");
require_once("model/AccountStorageMySQL.php");
require_once('/users/22010400/private/mysql_config.php');

/*
 * Cette page est simplement le point d'arrivée de l'internaute
 * sur notre site. On se contente de créer un routeur
 * et de lancer son main.
 */

$router = new Router();

/* on crée un objet pdo qui va nous permettre d'acceder à la base de donnees et de la manipuler*/

$dsn="mysql:host=".MYSQL_HOST.";port=".MYSQL_PORT.";dbname=".MYSQL_DB.";charset=utf8";
$user= MYSQL_USER;
$pass= MYSQL_PASSWORD;
$db = new PDO($dsn,$user,$pass);

/* on appele le main en passant comme arguments l'acces au tableau Movie et tableau des comptes utilisateurs */

$router->main(new MovieStorageMySQL($db), new AccountStorageMySQL($db));

?>
