<?php

   $database = new mysqli(
        "sql206.byethost31.com",            // MySQL Host Name
        "b31_39451563",                   // MySQL User Name
        "R0uss3au!",           // MySQL Password (replace with your actual vPanel password)
        "b31_39451563_safespaceph"       // MySQL DB Name (note the lowercase 'safespaceph')
    );

    if ($database->connect_error) {
        die("Connection failed: " . $database->connect_error);
    }
?>
