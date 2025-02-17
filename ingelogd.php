<?php
try {
    session_start();


    require "vendor/autoload.php";

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
    $host = $_ENV["host"];
    $usernameDb = $_ENV["username"];
    $passwordDb = $_ENV["password"];
    $database = $_ENV["database"];

    // Establish a connection with the database
    $connection = new mysqli($host, $usernameDb, $passwordDb, $database);
    if ($connection->connect_error) {
        throw new Exception("Database connection failed: " . $connection->connect_error);
    }

    // Prepare SELECT query to fetch the current login count for the user
    $querySelect = "SELECT Aantal_logins, blocked FROM gebruiker_uitgebreid WHERE Naam = ?";
    $statementSelect = $connection->prepare($querySelect);

    if ($statementSelect === false) {
        throw new Exception("Statement preparation failed: " . $connection->error);
    }

    $statementSelect->bind_param("s", $_SESSION['username']);

    // Execute the SELECT query
    if (!$statementSelect->execute()) {
        throw new Exception("Error executing SELECT query: " . $statementSelect->error);
    }

    // Bind result variable for SELECT query
    $statementSelect->bind_result($logins, $blocked);
    if ($blocked === 1 || $_SESSION["isBlocked"] === true) {
        $_SESSION["message"] = "<div class='error-message'>Je mag niet inloggen, je bent geblokkeerd </div>";
        header("location: blocked.php");
        exit();
    }
    // Fetch the result
    if (!$statementSelect->fetch()) {
        throw new Exception("No results found for user.");
    }

    // Increment login count
    $logins = $logins + 1;
    $laatste = date("Y-m-d H:i:s"); // MySQL datetime format

    // Close the SELECT statement before executing the UPDATE
    $statementSelect->close();

    // Check if the user is blocked (this assumes $_SESSION["isBlocked"] is set correctly)
    $blocked = ($_SESSION["isBlocked"] === true) ? 1 : 0;
    if ($_SESSION["isBlocked"] === true) {
        $blocked = 1;
    } elseif ($_SESSION["isBlocked"] === false) {
        $blocked = 0;
    }
    $blockedTijd = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // Prepare UPDATE query to update the login count and last login date
    $queryUpdate = "UPDATE gebruiker_uitgebreid SET Aantal_logins = ?, Laatste_login = ?, Blocked = ?, blockedTijd = ? WHERE Naam = ?";
    $statementUpdate = $connection->prepare($queryUpdate);
    if ($statementUpdate === false) {
        throw new Exception("Statement preparation failed: " . $connection->error);
    }

    // Bind the parameters for the UPDATE query
    $statementUpdate->bind_param("issss", $logins, $laatste, $blocked, $blockedTijd, $_SESSION['username']);

    // Execute the UPDATE query
    if (!$statementUpdate->execute()) {
        throw new Exception("Error executing UPDATE query: " . $statementUpdate->error);
    }

    // Close the UPDATE statement
    $statementUpdate->close();

    // Redirect to the user menu page
    header("Location: 2fa.php");
    exit();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
