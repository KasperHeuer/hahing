<?php
session_start();

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    require "vendor/autoload.php";

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();

    try {
        $host = $_ENV["host"];
        $usernameDb = $_ENV["username"];
        $passwordDb = $_ENV["password"];
        $database = $_ENV["database"];

        $connection = new mysqli($host, $usernameDb, $passwordDb, $database);
        if ($connection->connect_error) {
            throw new Exception("Databaseverbinding mislukt: " . $connection->connect_error);
        }

        $gebruikersnaam = htmlspecialchars($_POST["gebruikersnaam"]);
        $wachtwoord = htmlspecialchars($_POST["wachtwoord"]);

        $query = "SELECT Naam, Salting, Email, Hash_wachtwoord, blocked, blockedTijd FROM gebruiker_uitgebreid WHERE Naam = ?";
        $statement = $connection->prepare($query);
        $statement->bind_param("s", $gebruikersnaam);

        if ($statement->execute()) {
            $statement->bind_result($dbGebruikersnaam, $salt, $email, $dbWachtwoord, $blocked, $blockedTijd);

            if ($statement->fetch()) {
                if ($blocked == 1) {
                    $currentTime = new DateTime();
                    $blockedUntil = new DateTime($blockedTijd);

                    if ($currentTime < $blockedUntil) {
                        $remainingTime = $currentTime->diff($blockedUntil);
                        $timeLeft = $remainingTime->format('%h uur, %i minuten en %s seconden');
                        $_SESSION["message"] = "<div class='error-message'>Je bent geblokkeerd voor $timeLeft</div>";
                        header("Location: login.php");
                        exit();
                    } else {
                        $_SESSION["ingelogd"] = true;
                        $_SESSION["username"] = $gebruikersnaam;
                        $_SESSION["email"] = $email;
                        $_SESSION["message"] = "<div class='success-message'>Je bent niet langer geblokkeerd. Je kunt nu inloggen.</div>";
                        $_SESSION["password"] = $wachtwoord;
                        header("Location: ingelogd.php");
                        exit();
                    }
                }

                $wachtwoordSalt = $wachtwoord . $salt;

                if (!password_verify($wachtwoordSalt, $dbWachtwoord)) {
                    $_SESSION["blocked"] = ($_SESSION["blocked"] ?? 0) + 1;
                    $kansen = 3 - $_SESSION["blocked"];

                    $_SESSION["message"] = "<div class='error-message'>Wachtwoord is incorrect. Je hebt nog $kansen pogingen.</div>";

                    if ($_SESSION["blocked"] >= 3) {
                        $_SESSION["isBlocked"] = true;
                        $_SESSION["username"] = $gebruikersnaam;
                        $_SESSION["email"] = $email;
                        header("Location: ingelogd.php");
                        exit();
                    }
                } else {
                    $_SESSION["ingelogd"] = true;
                    $_SESSION["username"] = $gebruikersnaam;
                    $_SESSION["email"] = $email;
                    $_SESSION["password"] = $wachtwoord;
                    header("Location: ingelogd.php");
                    exit();
                }
            } else {
                $_SESSION["message"] = "<div class='error-message'>Gebruikersnaam niet gevonden.</div>";
            }
        } else {
            $_SESSION["message"] = "<div class='error-message'>Er is een fout opgetreden bij het inloggen.</div>";
        }
    } catch (Exception $e) {
        $_SESSION["message"] = "<div class='error-message'>Fout: " . $e->getMessage() . "</div>";
    } finally {
        $statement->close();
        $connection->close();
    }
} elseif ($method === "GET") {
    $_SESSION["blocked"] = 0;
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f4f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            justify-content: center;
        }

        .message-container {
            width: 100%;
            text-align: center;
            position: absolute;
            top: 20px;
            padding: 10px;
        }

        .success-message,
        .error-message {
            color: white;
            font-size: 16px;
            padding: 12px;
            border-radius: 6px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
            text-align: center;
            margin-bottom: 20px;
        }

        .success-message {
            background-color: #28a745;
        }

        .error-message {
            background-color: #dc3545;
        }

        .container {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 350px;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            color: #333;
        }

        .registration-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-label {
            font-size: 14px;
            text-align: left;
            color: #555;
        }

        .form-input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            width: 100%;
        }

        .submit-button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .submit-button:hover {
            background-color: #0056b3;
        }

        .register-link {
            margin-top: 15px;
            font-size: 14px;
        }

        .register-link a {
            color: #007bff;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <?php if (isset($_SESSION["message"])): ?>
        <div class="message-container">
            <?php echo $_SESSION["message"];
            unset($_SESSION["message"]); ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <h1>Inloggen</h1>
        <form action="login.php" method="POST" class="registration-form">
            <label for="gebruikersnaam" class="form-label">Gebruikersnaam:</label>
            <input type="text" name="gebruikersnaam" id="gebruikersnaam" placeholder="Voer je gebruikersnaam in" required class="form-input">

            <label for="wachtwoord" class="form-label">Wachtwoord:</label>
            <input type="password" name="wachtwoord" id="wachtwoord" placeholder="Voer je wachtwoord in" required class="form-input">

            <input type="submit" value="Inloggen" class="submit-button">
        </form>

        <p class="register-link">Nog geen account? <a href="index.php">Registreer hier</a></p>
    </div>

</body>

</html>