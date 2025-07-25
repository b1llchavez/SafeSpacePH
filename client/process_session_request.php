<?php
session_start();


include("../connection.php");
require ("../send_email.php"); // Include the email sending script


date_default_timezone_set('Asia/Manila');


if(!isset($_SESSION["user"]) || $_SESSION['usertype']!='c'){
    header("location: ../login.php");
    exit();
}


if(isset($_POST['submit_request'])){


    $title = mysqli_real_escape_string($database, $_POST['title']);
    $description = mysqli_real_escape_string($database, $_POST['description']);
    $preferred_date = mysqli_real_escape_string($database, $_POST['preferred_date']);
    $preferred_time = mysqli_real_escape_string($database, $_POST['preferred_time']);
    

    $clientid = $_SESSION['cid']; 
    $clientEmail = $_SESSION['user']; // The user's email is stored in the 'user' session variable
    $clientName = $_SESSION['cname']; // The client's name from the session


    if(empty($title) || empty($description) || empty($preferred_date) || empty($preferred_time) || empty($clientid)){
        header("location: client-appointment.php?action=error&message=".urlencode("Please fill all required fields."));
        exit();
    }


    mysqli_begin_transaction($database);

    try {

        // Insert into schedule first. The client is linked via the appointment table.
        // Removed `nop` and `clientid` as they are likely not in the `schedule` table schema, causing the error.
        $insert_schedule_query = "INSERT INTO schedule (lawyerid, title, scheduledate, scheduletime) 
                                  VALUES (NULL, '$title', '$preferred_date', '$preferred_time')";
        
        if (!mysqli_query($database, $insert_schedule_query)) {
            throw new Exception("Error creating schedule: " . mysqli_error($database));
        }


        $scheduleid = mysqli_insert_id($database);


        $apponum = time(); 


        $appodate = date('Y-m-d'); 
        // Insert into appointment with 'pending' status
        $insert_appointment_query = "INSERT INTO appointment (cid, apponum, scheduleid, appodate, status, description) 
                                     VALUES ('$clientid', '$apponum', '$scheduleid', '$appodate', 'pending', '$description')";
        
        if (!mysqli_query($database, $insert_appointment_query)) {
            throw new Exception("Error creating appointment: " . mysqli_error($database));
        }


        mysqli_commit($database);


        // Send email notification to the client that the request is pending
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

            // Log email error but don't stop the user flow
            error_log("Email sending failed: " . $e->getMessage());

        }


        // Redirect to the appointments page with a success message
        header("location: client-appointment.php?action=session-requested&title=".urlencode($title));
        exit();

    } catch (Exception $e) {

        mysqli_rollback($database);
        error_log("Session request error: " . $e->getMessage()); 
        header("location: client-appointment.php?action=error&message=".urlencode("Failed to submit session request. Please try again."));
        exit();
    }

} else {
    // Redirect back to the form if accessed directly
    header("location: request-session.php");
    exit();
}
?>
