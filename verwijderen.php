<!DOCTYPE html>
<html lang="nl">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gebruikersinformatie</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <?php

        try {
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

            $query = "SELECT id, naam FROM gebruiker_uitgebreid";
            $statement = $connection->prepare($query);

            if (!$statement) {
                throw new Exception("Prepare failed: " . $connection->error);
            }



            if (!$statement->execute()) {
                throw new Exception("Execution failed: " . $statement->error);
            }
            echo '<h1>Gebruikersinformatie</h1>';
            $statement->bind_result($id, $naam);
            while ($statement->fetch()) {
                echo "
                    <div class='user-info'>
                        <p><strong>Id:</strong> $id, $naam</p>
                        <a href='verwijder.php?id=$id' class='logout'>Verwijderen</a>
                    </div>
                ";
            }

            $statement->close();
            $connection->close();
        } catch (Exception $e) {
            $message = "<p class='error-message'>Error: " . $e->getMessage() . "</p>";
        }

        echo isset($message) ? $message : '';
        ?>
    </div>

</body>
<?php

?>

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
        flex-direction: column;
    }

    .container {
        background-color: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 450px;
        text-align: center;
        margin: 20px;
    }

    h1 {
        color: #4CAF50;
        margin-bottom: 25px;
        font-size: 28px;
        font-weight: 600;
    }

    .user-info {
        text-align: left;
        font-size: 16px;
        color: #333;
    }

    .user-info p {
        margin: 8px 0;
    }

    .user-info p strong {
        color: #007BFF;
    }

    .error-message {
        background-color: #dc3545;
        color: white;
        font-size: 16px;
        padding: 12px;
        border-radius: 6px;
        text-align: center;
        margin-top: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .user-info a {
        color: #42A5F5;
        text-decoration: none;
        font-weight: bold;
        transition: color 0.3s ease;
    }

    .user-info a:hover {
        color: #1E88E5;
        text-decoration: underline;
    }

    .logout {
        display: inline-block;
        padding: 12px 25px;
        background-color: #28a745;
        color: white;
        text-decoration: none;
        font-weight: bold;
        border-radius: 5px;
        transition: background-color 0.3s ease, transform 0.2s ease;


    }

    .logout:hover {
        background-color: #4CAF50;
        transform: scale(1.05);
    }

    .logout:active {
        background-color: #218838;
    }

    .edit-link {
        display: inline-block;
        padding: 12px 25px;
        background-color: #007BFF;
        color: white;
        text-decoration: none;
        font-weight: bold;
        border-radius: 5px;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .edit-link:hover {
        background-color: #0056b3;
        transform: scale(1.05);
    }

    .edit-link:active {
        background-color: #004085;
    }

    /* Responsive design */
    @media (max-width: 480px) {
        .container {
            width: 90%;
            padding: 20px;
        }

        h1 {
            font-size: 24px;
        }

        .user-info {
            font-size: 14px;
        }

        .edit-link,
        .logout {
            width: 100%;
            padding: 12px;
            font-size: 16px;
        }
    }
</style>