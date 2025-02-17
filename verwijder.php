<?php

session_start();
require "vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

if (!isset($_SESSION["ingelogd"]) || $_SESSION["ingelogd"] !== true) {
    header("Location: login.php");
    exit();
}

$host = $_ENV["host"];
$usernameDb = $_ENV["username"];
$passwordDb = $_ENV["password"];
$database = $_ENV["database"];

$connection = new mysqli($host, $usernameDb, $passwordDb, $database);
if ($connection->connect_error) {
    throw new Exception($connection->connect_error);
}

if (!isset($_SESSION['username'])) {
    throw new Exception("Username not found in session.");
}

$query = "DELETE FROM gebruiker_uitgebreid WHERE ID = ?";
$statement = $connection->prepare($query);
$statement->bind_param("i", $_GET['id']); // "i" for integer parameter
$statement->execute();

setcookie("bericht", "<div class='succesvol'>Succesvol verwijderd</div>", time() + 2);
header("location: edit.php");
exit();