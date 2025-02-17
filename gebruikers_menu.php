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

            $query = "SELECT * FROM gebruiker_uitgebreid WHERE Naam = ?";
            $statement = $connection->prepare($query);

            if (!$statement) {
                throw new Exception("Prepare failed: " . $connection->error);
            }

            $statement->bind_param("s", $_SESSION['username']);

            if (!$statement->execute()) {
                throw new Exception("Execution failed: " . $statement->error);
            }

            $statement->bind_result($id, $gebruikersnaam, $email, $voornaam, $achternaam, $salting, $hash_wachtwoord, $aantal_logins, $laatste_login, $blocked, $blockedTijd, $FACode, $rol_id);
            $wachtwoord = $_SESSION["password"];

            if ($statement->fetch()) {
                // After fetching, close the first statement
                $statement->close();



                $queryTwee = "SELECT * FROM rollen WHERE rolid = ?";
                $rolStatement = $connection->prepare($queryTwee);

                if (!$rolStatement) {
                    throw new Exception("Prepare failed for role query: " . $connection->error);
                }

                $rolStatement->bind_param("i", $rol_id);

                if (!$rolStatement->execute()) {
                    throw new Exception("Role query execution failed: " . $rolStatement->error);
                }

                // Bind the result to fetch role data
                $rolStatement->bind_result($rolId, $rolNaam, $rolOmschrijving, $rechten);

                // Try fetching the role data
                if ($rolStatement->fetch()) {
                    $rolStatement->close(); // Close the role statement after fetching the data
                } else {
                    // If no data is found, set default values
                    $rolNaam = 'Onbekend';
                    $rolOmschrijving = 'Geen omschrijving beschikbaar';
                    $rechten = 0;
                }

                // Display the user information
                $message = "
                    <div class='user-info'>
                        <h1>Gebruikersinformatie</h1>
                        <p><strong>Gebruikersnaam:</strong> $gebruikersnaam</p>
                        <p><strong>Voornaam:</strong> $voornaam</p>
                        <p><strong>Achternaam:</strong> $achternaam</p>
                        <p><strong>Email:</strong> $email</p>
                        <p><strong>Wachtwoord:</strong> $wachtwoord</p>
                        <p><strong>Aantal logins:</strong> $aantal_logins</p>
                        <p><strong>Laatste login:</strong> $laatste_login</p>
                        <p><strong>Rol:</strong> $rolNaam</p>
                    </div>
                ";
            } else {
                $message = "<p class='error-message'>Geen gebruiker gevonden met deze gebruikersnaam.</p>";
            }

            // Close the main connection
            $connection->close();
        } catch (Exception $e) {
            $message = "<p class='error-message'>Fout: " . $e->getMessage() . "</p>";
        }

        echo isset($message) ? $message : '';
        ?>
    </div>

    <a href="logout.php" class='logout'>Logout</a>

    <?php
    // Ensure that $rechten is set correctly
    if (isset($rechten) && $rechten >= 2) {
        $_SESSION["rechten"] = $rechten;
        echo "<a href='edit.php' class='edit-link'>Bewerk</a>";
    }
    ?>
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
        border-radius: 5px;
        text-align: center;
        margin-top: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .user-info a {
        color: #42A5F5;
        text-decoration: none;
        font-weight: bold;
    }

    .user-info a:hover {
        text-decoration: underline;
    }

    .logout {
        display: inline-block;
        padding: 10px 20px;
        background-color: #ff4b5c;
        color: white;
        text-decoration: none;
        font-weight: bold;
        border-radius: 5px;
        transition: background-color 0.3s ease, transform 0.2s ease;
        position: absolute;
        bottom: 10px;
    }

    .logout:hover {
        background-color: #e0434b;
        transform: scale(1.05);
    }

    .logout:active {
        background-color: #d0333a;
    }

    .edit-link {
        display: inline-block;
        padding: 10px 20px;
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
</style>