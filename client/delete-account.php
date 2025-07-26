<?php

session_start();

// Ensure user is logged in and is a client
if (!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'c') {
    header("location: ../login.php");
    exit(); // It's good practice to exit after a header redirect
}

$useremail = $_SESSION["user"];

include("../connection.php");
require_once("../send_email.php");

// Get details of the currently logged-in user
$userrow = $database->query("SELECT cid, cname FROM client WHERE cemail='$useremail'");
if ($userrow->num_rows > 0) {
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch["cid"];
    $username = $userfetch["cname"];
} else {
    // Handle case where user data is not found, though this should not happen if session is valid
    header("location: ../login.php");
    exit();
}


if (isset($_GET['id'])) {
    $id_to_delete = $_GET["id"];

    // Security check: Ensure the user is deleting their own account
    if ($id_to_delete != $userid) {
        // Redirect or show an error if a user tries to delete another user's account
        header("location: ./index.php?action=error");
        exit();
    }

    // Fetch the client's details from the database
    $result = $database->query("SELECT cemail, cname FROM client WHERE cid=$id_to_delete");

    if ($result->num_rows > 0) {
        // Fetch the entire row into a single variable
        $client_data = $result->fetch_assoc();
        $email_to_delete = $client_data["cemail"];
        $name_to_delete = $client_data["cname"];

        // Send the goodbye email with the correct name
        sendClientGoodbyeEmailOnAccountDeletion($email_to_delete, $name_to_delete);

        // Delete the user from both tables
        $database->query("DELETE FROM webuser WHERE email='$email_to_delete'");
        $database->query("DELETE FROM client WHERE cemail='$email_to_delete'");

        // Log the user out and redirect to the logout page
        header("location: ../logout.php");
        exit();
    } else {
        // Handle case where the client ID is not found
        header("location: ./index.php?action=error&message=ClientNotFound");
        exit();
    }
}

?>
