<?php
session_start();

// Include necessary files
include("../connection.php");
require ("../send_email.php"); // Include the email sending script

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
    $title = mysqli_real_escape_string($database, $_POST['title']);
    $description = mysqli_real_escape_string($database, $_POST['description']);
    $preferred_date = mysqli_real_escape_string($database, $_POST['preferred_date']);
    $preferred_time = mysqli_real_escape_string($database, $_POST['preferred_time']);
    
    // Retrieve client details from session
    $clientid = $_SESSION['cid']; 
    $clientEmail = $_SESSION['user']; // The user's email is stored in the 'user' session variable
    $clientName = $_SESSION['cname']; // The client's name from the session

    // Validate inputs
    if(empty($title) || empty($description) || empty($preferred_date) || empty($preferred_time) || empty($clientid)){
        header("location: client-appointment.php?action=error&message=".urlencode("Please fill all required fields."));
        exit();
    }

    // Start a database transaction
    mysqli_begin_transaction($database);

    try {
        // 1. Insert into the 'schedule' table
        $insert_schedule_query = "INSERT INTO schedule (lawyerid, title, scheduledate, scheduletime, nop, clientid) 
                                  VALUES (NULL, '$title', '$preferred_date', '$preferred_time', 1, '$clientid')";
        
        if (!mysqli_query($database, $insert_schedule_query)) {
            throw new Exception("Error creating schedule: " . mysqli_error($database));
        }

        // Get the last inserted scheduleid
        $scheduleid = mysqli_insert_id($database);

        // 2. Generate a unique appointment number
        $apponum = time(); 

        // 3. Insert into the 'appointment' table
        $appodate = date('Y-m-d'); 
        $insert_appointment_query = "INSERT INTO appointment (cid, apponum, scheduleid, appodate, status, description) 
                                     VALUES ('$clientid', '$apponum', '$scheduleid', '$appodate', 'pending', '$description')";
        
        if (!mysqli_query($database, $insert_appointment_query)) {
            throw new Exception("Error creating appointment: " . mysqli_error($database));
        }

        // Commit the transaction
        mysqli_commit($database);

        // 4. Send the confirmation email
        try {
            sendAppointmentPendingEmail(
                $clientEmail,
                $clientName,
                $preferred_date,
                $preferred_time,
                $title,
                $description,
                'To be Assigned', // Default lawyer name since this is pending
                'Online Consultation' // Default meeting type
            );
        } catch (Exception $e) {
            // Log the error but don't stop the process
            error_log("Email sending failed: " . $e->getMessage());
            // Continue with redirect - the appointment is still created
        }

        // Redirect to client appointments page with success message
        header("location: client-appointment.php?action=session-requested&title=".urlencode($title));
        exit();

    } catch (Exception $e) {
        // Rollback the transaction on error
        mysqli_rollback($database);
        error_log("Session request error: " . $e->getMessage()); 
        header("location: client-appointment.php?action=error&message=".urlencode("Failed to submit session request. Please try again."));
        exit();
    }

} else {
    // If accessed directly, redirect
    header("location: request-session.php");
    exit();
}
?>