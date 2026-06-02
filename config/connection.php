<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

class Connection{

	static public function connect(){

		$host = $_ENV['DB_HOST'];
		$db   = $_ENV['DB_DATABASE'];
		$user = $_ENV['DB_USERNAME'];
		$pass = $_ENV['DB_PASSWORD'];

		$link = new PDO("mysql:host=$host;dbname=$db",$user,$pass);

		$acentos = $link->query("SET NAMES 'utf8'");

		return $link;
	}
}