<?php
$method = $_SERVER["REQUEST_METHOD"];
require "vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

if ($method === "POST") {
    try {
        $host = $_ENV["host"];
        $usernameDb = $_ENV["username"];
        $passwordDb = $_ENV["password"];
        $database = $_ENV["database"];

        $connection = new mysqli($host, $usernameDb, $passwordDb, $database);
        if ($connection->connect_error) {
            throw new Exception($connection->connect_error);
        }

        $gebruikersnaam = htmlspecialchars($_POST["username"]);
        $email = htmlspecialchars($_POST["email"]);
        $wachtwoord = htmlspecialchars($_POST["password"]);
        $wachtwoordCheck = htmlspecialchars($_POST["confirm-password"]);

        if ($wachtwoord === $wachtwoordCheck) {
            $salting = uniqid();
            $wachtwoord = $wachtwoord . $salting;

            $querySelect = "SELECT Naam, Email FROM gebruiker_uitgebreid WHERE Naam = ? OR Email = ?";
            $statementSelect = $connection->prepare($querySelect);
            $statementSelect->bind_param("ss", $gebruikersnaam, $email);
            $statementSelect->execute();
            $statementSelect->bind_result($dbNaam, $dbEmail);

            if ($statementSelect->fetch()) {
                $message = "<p class='error-message'>Gebruikersnaam of email bestaat al.</p>";
            } else {
                $voornaam = htmlspecialchars($_POST["voornaam"]);
                $achternaam = htmlspecialchars($_POST["achternaam"]);
                $hashedWachtwoord = password_hash($wachtwoord, PASSWORD_DEFAULT);

                $queryInsert = "INSERT INTO gebruiker_uitgebreid (Naam, Email, voornaam, achternaam, Salting, Hash_wachtwoord, Aantal_Logins, Laatste_Login, Blocked, rechten_niveau) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $statementInsert = $connection->prepare($queryInsert);
                if ($statementInsert === false) {
                    throw new Exception("Statement preparation failed: " . $connection->error);
                }

                $aantal = 1;
                $laatste = date("Y-m-d");
                $blocked = 0;
                $niveau = 1;
                $statementInsert->bind_param("ssssssisii", $gebruikersnaam, $email, $voornaam, $achternaam, $salting, $hashedWachtwoord, $aantal, $laatste, $blocked, $niveau);

                if ($statementInsert->execute()) {
                    $message = "<p class='success-message'>Registratie geslaagd!</p>";
                    header("LOCATION: login.php");
                    die();
                } else {
                    $message = "<p class='error-message'>Er is een fout opgetreden bij het registreren.</p>";
                }
            }
            $statementSelect->close();
        } else {
            $message = "<p class='error-message'>Wachtwoorden zijn niet hetzelfde</p>";
        }
    } catch (Exception $e) {
        $message = "<p class='error-message'>Fout: " . $e->getMessage() . "</p>";
    } finally {
        if (isset($connection) && $connection) {
            $connection->close();
        }
        if (isset($statementInsert) && $statementInsert) {
            $statementInsert->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registratie</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .message-container {
            width: 100%;
            text-align: center;
            position: absolute;
            top: 20px;
            padding: 10px;
            z-index: 10;
        }

        .success-message {
            background-color: #28a745;
            color: white;
            font-size: 18px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            margin-bottom: 20px;
        }

        .error-message {
            background-color: #dc3545;
            color: white;
            font-size: 18px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            margin-bottom: 20px;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            color: #333;
        }

        .registration-form {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-size: 14px;
            margin-bottom: 5px;
            color: #333;
            text-align: left;
        }

        .form-input {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            width: 100%;
        }

        .submit-button {
            padding: 12px;
            background-color: #42A754;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
        }

        .submit-button:hover {
            background-color: #368b45;
        }

        .login-link {
            display: inline-block;
            margin-top: 15px;
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }

        .login-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="message-container">
        <?php if (isset($message)) echo $message; ?>
    </div>

    <div class="container">
        <h1>Registreren</h1>
        <form action="index.php" method="POST" class="registration-form">
            <label for="username" class="form-label">Gebruikersnaam:</label>
            <input type="text" name="username" id="username" placeholder="Voer je gebruikersnaam in" required class="form-input">

            <label for="email" class="form-label">Email:</label>
            <input type="email" name="email" id="email" placeholder="Voer je email in" required class="form-input">

            <label for="voornaam" class="form-label">Voornaam:</label>
            <input type="text" name="voornaam" id="voornaam" placeholder="Voer je voornaam in" required class="form-input">

            <label for="achternaam" class="form-label">Achternaam:</label>
            <input type="text" name="achternaam" id="achternaam" placeholder="Voer je achternaam in" required class="form-input">

            <label for="password" class="form-label">Wachtwoord:</label>
            <input type="password" name="password" id="password" placeholder="Voer je wachtwoord in" required class="form-input">

            <label for="confirm-password" class="form-label">Bevestig wachtwoord:</label>
            <input type="password" name="confirm-password" id="confirm-password" placeholder="Bevestig je wachtwoord" required class="form-input">

            <input type="submit" value="Registreren" class="submit-button">
        </form>
        <a href="login.php" class="login-link">Inloggen</a>
    </div>
</body>

</html>