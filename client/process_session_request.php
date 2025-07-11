<?php
session_start();

// Include database connection
include("../connection.php");

// Set timezone to Manila
date_default_timezone_set('Asia/Manila');

// Check if the user is logged in and is a client
if(!isset($_SESSION["user"]) || $_SESSION['usertype']!='c'){
    header("location: ../login.php");
    exit();
}

// Check if the form was submitted
if(isset($_POST['submit_request'])){

    // Retrieve and sanitize input data
    // REMOVED: $lawyerid = mysqli_real_escape_string($database, $_POST['lawyerid']);
    $title = mysqli_real_escape_string($database, $_POST['title']);
    $description = mysqli_real_escape_string($database, $_POST['description']);
    $preferred_date = mysqli_real_escape_string($database, $_POST['preferred_date']);
    $preferred_time = mysqli_real_escape_string($database, $_POST['preferred_time']);
    $clientid = $_SESSION['cid']; // Get client ID from session

    // Validate inputs (basic validation)
    // REMOVED: || empty($lawyerid)
    if(empty($title) || empty($description) || empty($preferred_date) || empty($preferred_time) || empty($clientid)){
        header("location: client-appointment.php?action=error&message=".urlencode("Please fill all required fields."));
        exit();
    }

    // Start a database transaction for atomicity
    mysqli_begin_transaction($database);

    try {
        // 1. Insert into the 'schedule' table
        // lawyerid is set to NULL as no lawyer is assigned at this stage
        $insert_schedule_query = "INSERT INTO schedule (lawyerid, title, scheduledate, scheduletime, nop, clientid) 
                                  VALUES (NULL, '$title', '$preferred_date', '$preferred_time', 1, '$clientid')";
        
        if (!mysqli_query($database, $insert_schedule_query)) {
            throw new Exception("Error creating schedule: " . mysqli_error($database));
        }

        // Get the last inserted scheduleid
        $scheduleid = mysqli_insert_id($database);

        // 2. Generate a unique appointment number (simple timestamp-based for now)
        $apponum = time(); 

        // 3. Insert into the 'appointment' table
        // Set status to 'pending' by default
        // The 'description' field is assumed to be added to the 'appointment' table.
        $appodate = date('Y-m-d'); 

        $insert_appointment_query = "INSERT INTO appointment (cid, apponum, scheduleid, appodate, status, description) 
                                     VALUES ('$clientid', '$apponum', '$scheduleid', '$appodate', 'pending', '$description')";
        
        if (!mysqli_query($database, $insert_appointment_query)) {
            throw new Exception("Error creating appointment: " . mysqli_error($database));
        }

        // Commit the transaction
        mysqli_commit($database);

        // Redirect to client appointments page with success message
        header("location: client-appointment.php?action=session-requested&title=".urlencode($title));
        exit();

    } catch (Exception $e) {
        // Rollback the transaction on error
        mysqli_rollback($database);
        // Redirect to client appointments page with error message
        error_log("Session request error: " . $e->getMessage()); 
        header("location: client-appointment.php?action=error&message=".urlencode("Failed to submit session request. Please try again. Error: " . $e->getMessage()));
        exit();
    }

} else {
    // If accessed directly without POST data, redirect to request session page
    header("location: request-session.php");
    exit();
}
?>