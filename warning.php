<?php

session_start();


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "vendor/autoload.php";
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();

    if (!isset($_SESSION["username"], $_SESSION["email"])) {
        throw new Exception("Gebruiker of e-mail niet ingelogd.");
    }


    $to = "40208910@yonder.nl";

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Ongeldig e-mailadres.");
    }

    $mail = new PHPMailer(true);

    try {
        $priveEmail = $_ENV["Email"];
        $priveWachtwoord = $_ENV["Wachtwoord"];
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = $priveEmail;
        $mail->Password = $priveWachtwoord;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($priveEmail, "Beveiliging");
        $mail->addAddress($to);
        $account = $_SESSION['username'];
        $mail->Subject = "Er is een account geblokkeerd";
        $mail->Body = "Het account van $account is voor 1 uur geblokkeerd.";

        $mail->send();
        $email = $_SESSION["email"];
        echo "Er is een bericht naar uw mail verstuurd naar $to.";
        header("Location: login.php");
    } catch (Exception $e) {
        throw new Exception("Fout bij het verzenden van de e-mail: " . $mail->ErrorInfo);
    }
} catch (Exception $e) {
    echo "De error is" . $e->getMessage();
}
