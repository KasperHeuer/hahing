<?php
session_start();
if (!isset($_SESSION["ingelogd"]) || $_SESSION["ingelogd"] !== true) {
    header("Location: login.php");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$method = $_SERVER["REQUEST_METHOD"];
$host = $_ENV["host"];
$usernameDb = $_ENV["username"];
$passwordDb = $_ENV["password"];
$database = $_ENV["database"];

try {
    $connection = new mysqli($host, $usernameDb, $passwordDb, $database);
    if ($connection->connect_error) {
        throw new Exception("Database verbinding mislukt: " . $connection->connect_error);
    }

    if ($method === "POST") {
        if (!isset($_SESSION['username'])) {
            throw new Exception("Gebruiker niet ingelogd.");
        }

        $querySelect = "SELECT FACode FROM gebruiker_uitgebreid WHERE naam = ?";
        $statementSelect = $connection->prepare($querySelect);
        if ($statementSelect === false) {
            throw new Exception("Statement preparation failed: " . $connection->error);
        }

        $statementSelect->bind_param("s", $_SESSION['username']);
        $statementSelect->execute();
        $statementSelect->bind_result($dbFACode);
        $statementSelect->fetch();
        $statementSelect->close();

        if (!$dbFACode) {
            throw new Exception("Geen 2FA-code gevonden voor gebruiker.");
        }

        $POSTFACode = htmlspecialchars($_POST["2fa_code"]);

        if ($dbFACode === $POSTFACode) {
            header("Location: gebruikers_menu.php");
            exit;
        } else {
            $_SESSION["message"] = "Verkeerde code. Probeer opnieuw.";
        }
    } elseif ($method === "GET") {
        if (!isset($_SESSION['username'], $_SESSION['email'])) {
            throw new Exception("Gebruiker of e-mail niet ingelogd.");
        }

        $FACode = uniqid();

        $queryUpdate = "UPDATE gebruiker_uitgebreid SET FACode = ? WHERE naam = ?";
        $statementUpdate = $connection->prepare($queryUpdate);
        if ($statementUpdate === false) {
            throw new Exception("Statement preparation failed: " . $connection->error);
        }

        $statementUpdate->bind_param("ss", $FACode, $_SESSION["username"]);
        if (!$statementUpdate->execute()) {
            throw new Exception("Fout bij uitvoeren UPDATE: " . $statementUpdate->error);
        }
        $statementUpdate->close();

        // Email credentials
        $PriveEmail = $_ENV["Email"];
        $PriveWachtwoord = $_ENV["Wachtwoord"];

        // Validate recipient email
        $to = $_SESSION["email"];
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Ongeldig e-mailadres.");
        }

        // Send email via PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = $PriveEmail;
            $mail->Password = $PriveWachtwoord;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($PriveEmail, "Beveiliging");
            $mail->addAddress($to);
            $mail->Subject = "Uw 2FA Code";
            $mail->Body = "Uw code is: " . $FACode;

            $mail->send();
            
            $_SESSION["message"] = "Er is een code naar uw e-mail verstuurd naar $to.";
        } catch (Exception $e) {
            $_SESSION["message"] = "Fout bij het verzenden van de e-mail: " . $mail->ErrorInfo;
        }
    }
} catch (Exception $e) {
    $_SESSION["message"] = "Fout: " . $e->getMessage();
} finally {
    $connection->close();
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Verificatie</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <h2>2FA Verificatie</h2>
        <?php if (isset($_SESSION["message"])): ?>
            <div class="message"><?php echo $_SESSION["message"];
                                    unset($_SESSION["message"]); ?></div>
        <?php endif; ?>
        <form action="2fa.php" method="POST" class="form">
            <label for="2fa-code">Voer uw 2FA-code in</label>
            <input type="text" id="2fa-code" name="2fa_code" required class="form-input">
            <input type="submit" value="Verifiëren" class="form-button">
        </form>
        <p class="info">Controleer uw e-mail voor de 2FA-code.</p>
    </div>
</body>

</html>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    body {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background-color: #f4f4f4;
    }

    .container {
        background: white;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        text-align: center;
        width: 100%;
        max-width: 400px;
    }

    h2 {
        margin-bottom: 1rem;
        color: #333;
    }

    .message {
        background-color: #ffdddd;
        color: #d8000c;
        padding: 10px;
        border: 1px solid #d8000c;
        border-radius: 5px;
        margin-bottom: 1rem;
    }

    label {
        font-size: 1rem;
        color: #555;
        display: block;
        margin-bottom: 0.5rem;
        text-align: left;
    }

    .form-input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 1rem;
        margin-bottom: 1rem;
    }

    .form-button {
        width: 100%;
        padding: 10px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 1rem;
        cursor: pointer;
        transition: 0.3s;
    }

    .form-button:hover {
        background-color: #0056b3;
    }

    .info {
        font-size: 0.9rem;
        color: #777;
        margin-top: 1rem;
    }
</style>