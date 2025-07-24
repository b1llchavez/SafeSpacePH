<?php
session_start();

if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'l') {
        header("location: ../login.php");
        exit();
    }
} else {
    header("location: ../login.php");
    exit();
}

// Import dependencies
include("../connection.php");
require_once '../send_email.php'; // Ensure this path is correct

if (isset($_GET["id"])) {
    $id = $_GET["id"];

    // 1. Get user details before deleting
    $user_res = $database->query("select lawyername, lawyeremail from lawyer where lawyerid='$id'");
    if ($user_res->num_rows > 0) {
        $user_data = $user_res->fetch_assoc();
        $recipientName = $user_data['lawyername'];
        $recipientEmail = $user_data['lawyeremail'];

        // 2. Send the goodbye email
        sendGoodbyeEmailOnAccountDeletion($recipientEmail, $recipientName);

        // 3. Delete the lawyer from the database
        $database->query("delete from lawyer where lawyerid='$id';");

        // 4. Log out and redirect
        header("location: ../logout.php");
        exit();
    } else {
        // Handle case where user ID is not found
        header("location: settings.php?action=drop&id=" . $id . "&error=1");
        exit();
    }
} else {
    // Redirect if no ID is provided
    header("location: settings.php");
    exit();
}
?>