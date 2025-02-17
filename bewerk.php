<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Updaten</title>
    <?php

    $method = $_SERVER["REQUEST_METHOD"];

    $host = $_ENV["host"];
    $usernameDb = $_ENV["username"];
    $passwordDb = $_ENV["password"];
    $database = $_ENV["database"];


    try {

        if ($methode == "GET") {
            if (isset($_GET["idProduct"])) {

                $setId = $_GET["idProduct"];
                $setNaam = "<error>";
                $setKosten = "<error>";
                $setNummer = "<error>";
                // hier wordt alle informatie doorgegeven waarmee de code in de database kan
                $connectie = new mysqli($gastheer, $gebruikerVoorDatabase, $wachtwoordVoorDatabase, $gegevensbestand);
                // als er een connectie error is "gooit" de code de error naar de catch functie, die later in de code staat
                if ($connectie->connect_error) {
                    throw new Exception($connectie->connect_error);
                }
                // hier wordt alles geselecteerd van de tabel producten
                $vraag = "SELECT * FROM producten WHERE idProduct = ?";
                // hier wordt de code voorbereid
                $stelling = $connectie->prepare($vraag);
                // hier wordt de parmaters $setId omgezet naar integer/nummer waardoor het leesbaar is door de database
                $stelling->bind_param("i", $setId);
                // hier worden de resultaten van de $vraag omgezet in de variabelen $id, $naam, $kosten, $nummer, $mimeType en $afbeelding
                $stelling->bind_result($id, $naam, $kosten, $nummer, $mimeType, $abeelding);
                // hier als de variabele $stelleing niet wordt uitgevoerd, dan wordt de error "gegooit" naar de catch functie, die later in de code staat
                if (!$stelling->execute()) {
                    throw new Exception($connectie->error);
                }
                // terwijl dat de variabele $stelling bezig is met het ophalen van informatie wordt de code uitgevoerd
                while ($stelling->fetch()) {
                    // hier wordt de informatie in de variabelen $setId, $setNaam, $setKosten en $setNummer vernadert naar de informatie uit $id, $naam, $kosten en $nummer
                    $setId = $id;
                    $setNaam = $naam;
                    $setKosten = $kosten;
                    $setNummer = $nummer;
                }
            } else {
                // hier wordt de gebruiker gestuurd naar legoProducten.php
                header("location: legoProducten.php");
            }
        }
        // als de variabele $methode "POST" is dan wordt de code uitgevoerd
        else if ($methode = "POST") {
            // als de variabele $_POST["id"] bestaat dan wordt de code uitgevoerd
            if (isset($_POST["id"])) {
                // hier wordt in de variabele $postIdSet. $postSetNaam, $postSetKosten en $postSetNummer de variabele $_POST["id"], $_POST["naam"], $_POST["kosten"] en $_POST["nummer"] ingevoegd
                $postIdSet = $_POST["id"];
                $postSetNaam = $_POST["naam"];
                $postSetKosten = $_POST["kosten"];
                $postSetNummer = $_POST["nummer"];

                // hier wordt alle informatie doorgegeven waarmee de code in de database kan
                $connectie = new mysqli($gastheer, $gebruikerVoorDatabase, $wachtwoordVoorDatabase, $gegevensbestand);
                // als er een connectie error is "gooit" de code de error naar de catch functie, die later in de code staat
                if ($connectie->connect_error) {
                    throw new Exception($connectie->connect_error);
                }
                // hier wordt de informatie van productNaam, productKosten en setNummer veranderd die in de database staat
                $vraag = "UPDATE producten SET productNaam = ?, productKosten = ?, setNummer = ? WHERE idProduct  = ?";
                // hier wordt de voorbereid
                $stelling = $connectie->prepare($vraag);
                // hier worden de parmaters $postSetNaam, $postSetKosten, $postIdSet en $postIdSet gezet naar string/text, integer/nummer, integer/nummer en integer/nummer waardoor het leesbaar is door de database
                $stelling->bind_param("siii", $postSetNaam, $postSetKosten, $postSetNummer, $postIdSet);
                // hier als de varia"bele $stelleing niet wordt uitgevoerd, dan wordt de error "gegooit" naar de catch functie, die later in de code staat
                if (!$stelling->execute()) {
                    throw new Exception($connectie->error);
                }
                // hier wordt de gebruiker terug gestuurd naar legoProducten.php
                header("location: legoProducten.php");
                // de bericht wordt gestuurd dat de update functie succesvol is
                setcookie("bericht", "<div class=succesvol>Succesvolle update</div>", time() + 2);
            }
        }
    }

    // dit is de catch functie wat de errors "opvangt" en de error laat zien
    catch (Exception $e) {
        echo "De error is " . $e->getMessage();
    } finally {
        // als de variabele $connectie bestaat dan sluit de code de variabele
        if ($connectie) {
            $connectie->close();
        }
        // als de variabaele $stelling bestaat dan sluit de code de variabele
        if ($stelling) {
            $stelling->close();
        }
    }
    ?>
</head>

<body>
    <!-- hier wordt de forum aangemaakt waar de gebruiker kan invoeren wat die wilt veranderen -->
    <form action="legoUpdate.php" method="POST" class="update">
        <h1> Update</h1>

        <input type="hidden" name="id" value="<?php echo $setId; ?>">
        <label>Naam</label>
        <input type="text" name="naam" placeholder="Naam" value="<?php echo $setNaam; ?>"><br>
        <label>Kosten</label>
        <input type="text" name="kosten" placeholder="Kosten" value="<?php echo $setKosten; ?>"><br>
        <label>Nummer</label>
        <input type="text" name="nummer" placeholder="Nummer" value="<?php echo $setNummer; ?>"><br>
        <input type="submit" value="Update">
        <a href="legoProducten.php" class="terug">Terug</a>
    </form>
</body>

</html>