<?php
    $database = new mysqli("localhost", "root", "", "SafeSpacePH");
    if ($database->connect_error) {
        die("Connection failed: " . $database->connect_error);
    }
?>
