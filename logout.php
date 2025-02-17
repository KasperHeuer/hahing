<?php
session_start();
if (!isset($_SESSION["ingelogd"]) || $_SESSION["ingelogd"] !== true) {
    header("Location: login.php");
    exit();
} else {
    session_destroy();
    header("Location: login.php");
    exit();
}
