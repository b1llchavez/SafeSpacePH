<?php
//-Scythe | 2024-07-25
// Purpose: Handles updating client account information from settings.php

// Start the session to allow modification of session variables
session_start();

// Include the database connection file
include("../connection.php");

// Check if the form was submitted using the POST method
if ($_POST) {
    // Sanitize and retrieve POST data
    $id = $_POST['id00'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $oldemail = $_POST["oldemail"];
    $address = $_POST['address'];
    $tele = $_POST['Tele'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    // Default error code '3' for unexpected issues
    $error = '3';

    // --- Password Validation ---
    // Check if a new password was entered. If so, it must match the confirmation.
    if (!empty($password)) {
        if ($password != $cpassword) {
            // Passwords do not match, set error and stop processing.
            $error = '2';
            header("location: settings.php?action=edit&error=" . $error . "&id=" . $id);
            exit();
        }
    }

    // --- Email Validation ---
    // Check if the new email address is already in use by another account
    $check_email_sql = "SELECT * FROM webuser WHERE email= '$email';";
    $result = $database->query($check_email_sql);

    // If the email exists and it's not the user's current email, then it's taken
    if ($result->num_rows > 0 && $email != $oldemail) {
        $error = '1'; // Error code for email already exists
    } else {
        // --- Database Update ---
        // Construct the base SQL query for fields that are always updated.
        $update_client_sql = "UPDATE client SET cname='$name', cemail='$email', caddress='$address', ctel='$tele'";
        $update_webuser_sql = "UPDATE webuser SET email='$email'";

        // *** CORE FIX: Only add the password to the update query if a new one was provided. ***
        if (!empty($password)) {
            $update_client_sql .= ", cpassword='$password'";
            $update_webuser_sql .= ", password='$password'";
        }

        // Add the WHERE clause to complete the queries
        $update_client_sql .= " WHERE cid=$id;";
        $update_webuser_sql .= " WHERE email='$oldemail';";

        // Execute the update queries
        $database->query($update_client_sql);
        $database->query($update_webuser_sql);

        // Update the session with the new email address to prevent login issues
        $_SESSION["user"] = $email;

        $error = '4'; // Success code
    }
} else {
    // If the form was not submitted via POST, set a generic error
    $error = '3';
}

// Redirect the user back to the settings page with the appropriate error/success code and user ID
header("location: settings.php?action=edit&error=" . $error . "&id=" . $id);
exit(); // Ensure no further code is executed after redirection
?>
