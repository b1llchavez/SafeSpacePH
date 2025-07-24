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

include("../connection.php");
require_once '../send_email.php';

if (isset($_GET["id"])) {
    $id = $_GET["id"];

    $user_res = $database->query("select lawyername, lawyeremail from lawyer where lawyerid='$id'");
    if ($user_res->num_rows > 0) {
        $user_data = $user_res->fetch_assoc();
        $recipientName = $user_data['lawyername'];
        $recipientEmail = $user_data['lawyeremail'];

        sendGoodbyeEmailOnAccountDeletion($recipientEmail, $recipientName);

        $database->query("delete from lawyer where lawyerid='$id';");

        header("location: ../logout.php");
        exit();
    } else {
        header("location: settings.php?action=drop&id=" . $id . "&error=1");
        exit();
    }
} else {
    header("location: settings.php");
    exit();
}
?>