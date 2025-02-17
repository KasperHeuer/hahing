<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Updaten</title>
    <?php
    session_start();

    if (!isset($_SESSION["ingelogd"]) || $_SESSION["ingelogd"] !== true) {
        header("Location: login.php");
        exit();
    }

    require "vendor/autoload.php";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
    $method = $_SERVER["REQUEST_METHOD"];

    $host = $_ENV["host"];
    $usernameDb = $_ENV["username"];
    $passwordDb = $_ENV["password"];
    $database = $_ENV["database"];

    try {
        if ($method == "GET") {
            if (isset($_GET["id"])) {
                $Id = $_GET["id"];
                $Naam = "<error>";
                $Email = "<error>";
                $voornaam = "<error>";
                $achternaam = "<error>";
                $blocked = "<error>";
                $rechten = "<error>";

                $connection = new mysqli($host, $usernameDb, $passwordDb, $database);

                if ($connection->connect_error) {
                    throw new Exception($connection->connect_error);
                }

                $query = "SELECT Naam, Email, voornaam, Blocked, rechten_niveau FROM gebruiker_uitgebreid WHERE ID = ?";
                $statement = $connection->prepare($query);
                $statement->bind_param("i", $_GET["id"]);
                $statement->bind_result($naam, $email, $voornaam, $blocked, $rechten);

                if (!$statement->execute()) {
                    throw new Exception($connection->error);
                }

                while ($statement->fetch()) {
                    // Set the variables to the fetched values
                    $setId = $Id;
                    $setNaam = $naam;
                    $setEmail = $email;
                    $setBlocked = $blocked;
                    $setRechten = $rechten;
                }
            } else {
                header("location: gebruikers_menu.php");
                exit();
            }
        } else if ($method == "POST") {
            if (isset($_POST["id"])) {
                $postId = $_POST["id"];
                $postNaam = $_POST["naam"];
                $postEmail = $_POST["Email"];
                $postBlocked = $_POST["blocked"];
                $postRechten = $_POST["rechten"];

                $connection = new mysqli($host, $usernameDb, $passwordDb, $database);

                if ($connection->connect_error) {
                    throw new Exception($connection->connect_error);
                }

                $query = "UPDATE gebruiker_uitgebreid SET Naam = ?, Email = ?, blocked = ?, rechten_niveau = ? WHERE ID = ?";
                $statement = $connection->prepare($query);
                $statement->bind_param("sssii", $postNaam, $postEmail, $postBlocked, $postRechten, $postId);

                if (!$statement->execute()) {
                    throw new Exception($connection->error);
                }

                setcookie("bericht", "<div class='succesvol'>Succesvolle update</div>", time() + 2);
                header("location: edit.php");
                exit();
            }
        }
    } catch (Exception $e) {
        echo "De error is " . $e->getMessage();
    } finally {
        if (isset($connection)) {
            $connection->close();
        }
        if (isset($statement)) {
            $statement->close();
        }
    }
    ?>
</head>

<body>
    <!-- Update form -->
    <form action="bewerk.php" method="POST" class="update">
        <h1>Update</h1>
        <input type="hidden" name="id" value="<?php echo $setId; ?>">

        <label>Naam</label>
        <input type="text" name="naam" placeholder="Naam" value="<?php echo $setNaam; ?>"><br>

        <label>Email</label>
        <input type="text" name="Email" placeholder="Email" value="<?php echo $setEmail; ?>"><br>

        <label>Blocked</label>
        <input type="text" name="blocked" placeholder="Blocked" value="<?php echo $setBlocked; ?>"><br>

        <label>Rechten</label>
        <input type="text" name="rechten" placeholder="Rechten" value="<?php echo $setRechten; ?>"><br>

        <input type="submit" value="Update">
        
    </form>
</body>

</html>

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

    .update {
        background-color: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 450px;
        text-align: center;
    }

    h1 {
        color: #4CAF50;
        margin-bottom: 20px;
        font-size: 28px;
        font-weight: 600;
    }

    label {
        font-size: 16px;
        color: #333;
        margin-bottom: 5px;
        display: block;
        text-align: left;
        padding-left: 10px;
    }

    input[type="text"] {
        width: 100%;
        padding: 10px;
        margin: 8px 0;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 16px;
        box-sizing: border-box;
    }

    input[type="submit"] {
        width: 100%;
        padding: 12px;
        background-color: #28a745;
        color: white;
        font-weight: bold;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    input[type="submit"]:hover {
        background-color: #4CAF50;
        transform: scale(1.05);
    }

    input[type="submit"]:active {
        background-color: #218838;
    }

    .terug {
        display: inline-block;
        padding: 12px 25px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        font-weight: bold;
        border-radius: 5px;
        transition: background-color 0.3s ease, transform 0.2s ease;
        margin-top: 15px;
    }

    .terug:hover {
        background-color: #0056b3;
        transform: scale(1.05);
    }

    .terug:active {
        background-color: #004085;
    }

    .succesvol {
        background-color: #28a745;
        color: white;
        padding: 12px;
        border-radius: 5px;
        margin-top: 20px;
        text-align: center;
    }

    .error-message {
        background-color: #dc3545;
        color: white;
        padding: 12px;
        border-radius: 5px;
        text-align: center;
        margin-top: 20px;
    }

    /* Responsive design */
    @media (max-width: 480px) {
        .update {
            width: 90%;
            padding: 20px;
        }

        h1 {
            font-size: 24px;
        }

        input[type="text"],
        input[type="submit"] {
            font-size: 14px;
        }

        .terug {
            width: 100%;
            padding: 12px;
            font-size: 16px;
        }
    }
</style>