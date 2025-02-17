<?php
session_start();

if(isset($_COOKIE["succesvol"])){
    echo $_COOKIE["succesvol"];
}
if (!isset($_SESSION["ingelogd"]) || $_SESSION["ingelogd"] !== true) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["rechten"] >= 2) {
    echo "<a href='index.php' class='edit-link'>Toevoegen</a><br>";
}
if ($_SESSION["rechten"] >= 3) {
    echo "<a href='bewerken.php' class='edit-link'>Bewerken</a><br>";
}
if ($_SESSION["rechten"] >= 4) {
    echo "<a href='verwijderen.php' class='edit-link'>Verwijderen</a><br>";
}
if ($_SESSION["rechten"] < 2) {
    header("location: gebruikers_menu.php");
    exit();
}
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .container {
        background-color: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        width: 450px;
        text-align: center;
    }

    h1 {
        color: #4CAF50;
        margin-bottom: 20px;
        font-size: 26px;
    }

    .edit-link {
        display: inline-block;
        padding: 10px 20px;
        background-color: #007BFF;
        color: white;
        text-decoration: none;
        font-weight: bold;
        border-radius: 5px;
        margin: 5px 0;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .edit-link:hover {
        background-color: #0056b3;
        transform: scale(1.05);
    }

    .edit-link:active {
        background-color: #004085;
    }

    .error-message {
        background-color: #dc3545;
        color: white;
        font-size: 16px;
        padding: 12px;
        border-radius: 5px;
        text-align: center;
        margin-top: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
</style>