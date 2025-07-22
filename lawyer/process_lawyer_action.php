<?php
session_start();

// Include database connection
include("../connection.php");

// Set timezone to Manila
date_default_timezone_set('Asia/Manila');

// Check if the user is logged in and is a 'lawyer'
if (!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'l') {
    header("location: ../login.php");
    exit();
}

// Get lawyer ID from session
$lawyerid = $_SESSION['lawyerid'];

// Check if the required GET parameters are set
if (isset($_GET['action']) && isset($_GET['appoid'])) {
    $action = mysqli_real_escape_string($database, $_GET['action']);
    $appoid = mysqli_real_escape_string($database, $_GET['appoid']);
    $scheduleid = isset($_GET['scheduleid']) ? mysqli_real_escape_string($database, $_GET['scheduleid']) : null;

    // Start a database transaction for atomicity
    mysqli_begin_transaction($database);

    try {
        if ($action == 'accept') {
            // Validate scheduleid is provided for acceptance
            if (empty($scheduleid)) {
                throw new Exception("Schedule ID is required to accept a session.");
            }

            // 1. Update the schedule table to assign the lawyer
            // Ensure this update only happens if the lawyerid is currently NULL or 0 (unassigned)
            $update_schedule_query = "UPDATE schedule 
                                      SET lawyerid = '$lawyerid' 
                                      WHERE scheduleid = '$scheduleid' AND (lawyerid IS NULL OR lawyerid = '0')"; // Added check for NULL or '0'
            
            if (!mysqli_query($database, $update_schedule_query)) {
                throw new Exception("Error updating schedule: " . mysqli_error($database));
            }

            // Check if the schedule was actually updated (i.e., it was unassigned)
            if (mysqli_affected_rows($database) === 0) {
                // If 0 rows were affected, it means the session was already assigned or scheduleid was wrong.
                // We should check the current lawyerid in schedule to give a more specific error.
                $check_schedule_lawyer = $database->query("SELECT lawyerid FROM schedule WHERE scheduleid = '$scheduleid'");
                $current_lawyer = $check_schedule_lawyer->fetch_assoc()['lawyerid'];
                if ($current_lawyer == $lawyerid) {
                    throw new Exception("You have already accepted this session.");
                } elseif ($current_lawyer !== NULL && $current_lawyer !== '0') {
                    throw new Exception("This session has already been accepted by another lawyer.");
                } else {
                    throw new Exception("Failed to update schedule for acceptance. Schedule ID might be invalid.");
                }
            }

            // 2. Update the appointment status to 'accepted'
            $update_appointment_query = "UPDATE appointment 
                                         SET status = 'accepted' 
                                         WHERE appoid = '$appoid'";
            
            if (!mysqli_query($database, $update_appointment_query)) {
                throw new Exception("Error updating appointment status to accepted: " . mysqli_error($database));
            }

            // Commit the transaction
            mysqli_commit($database);
            header("location: manage-appointments.php?action=accepted&appoid=".urlencode($appoid));
            exit();

        } elseif ($action == 'reject') {
            // 1. Update the appointment status to 'rejected'
            $update_appointment_query = "UPDATE appointment 
                                         SET status = 'rejected' 
                                         WHERE appoid = '$appoid'";
            
            if (!mysqli_query($database, $update_appointment_query)) {
                throw new Exception("Error updating appointment status to rejected: " . mysqli_error($database));
            }

            // Commit the transaction
            mysqli_commit($database);
            header("location: manage-appointments.php?action=rejected&appoid=".urlencode($appoid));
            exit();

        } else {
            throw new Exception("Invalid action specified.");
        }

    } catch (Exception $e) {
        // Rollback the transaction on error
        mysqli_rollback($database);
        error_log("Lawyer action error: " . $e->getMessage()); // Log the error for debugging
        header("location: manage-appointments.php?action=error&message=".urlencode("Action failed: " . $e->getMessage()));
        exit();
    }

} else {
    // If accessed directly without required parameters
    header("location: manage-appointments.php?action=error&message=".urlencode("Invalid request."));
    exit();
}
?>