<?php
session_start();

include("../connection.php");

date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'l') {
    header("location: ../login.php");
    exit();
}

$lawyerid = $_SESSION['lawyerid'];

if (isset($_GET['action']) && isset($_GET['appoid'])) {
    $action = mysqli_real_escape_string($database, $_GET['action']);
    $appoid = mysqli_real_escape_string($database, $_GET['appoid']);
    $scheduleid = isset($_GET['scheduleid']) ? mysqli_real_escape_string($database, $_GET['scheduleid']) : null;

    mysqli_begin_transaction($database);

    try {
        if ($action == 'accept') {
            if (empty($scheduleid)) {
                throw new Exception("Schedule ID is required to accept a session.");
            }

            $update_schedule_query = "UPDATE schedule 
                                      SET lawyerid = '$lawyerid' 
                                      WHERE scheduleid = '$scheduleid' AND (lawyerid IS NULL OR lawyerid = '0')"; // Added check for NULL or '0'
            
            if (!mysqli_query($database, $update_schedule_query)) {
                throw new Exception("Error updating schedule: " . mysqli_error($database));
            }

            if (mysqli_affected_rows($database) === 0) {
              
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

            $update_appointment_query = "UPDATE appointment 
                                         SET status = 'accepted' 
                                         WHERE appoid = '$appoid'";
            
            if (!mysqli_query($database, $update_appointment_query)) {
                throw new Exception("Error updating appointment status to accepted: " . mysqli_error($database));
            }

            mysqli_commit($database);
            header("location: manage-appointments.php?action=accepted&appoid=".urlencode($appoid));
            exit();

        } elseif ($action == 'reject') {
            $update_appointment_query = "UPDATE appointment 
                                         SET status = 'rejected' 
                                         WHERE appoid = '$appoid'";
            
            if (!mysqli_query($database, $update_appointment_query)) {
                throw new Exception("Error updating appointment status to rejected: " . mysqli_error($database));
            }

            mysqli_commit($database);
            header("location: manage-appointments.php?action=rejected&appoid=".urlencode($appoid));
            exit();

        } else {
            throw new Exception("Invalid action specified.");
        }

    } catch (Exception $e) {
        mysqli_rollback($database);
        error_log("Lawyer action error: " . $e->getMessage());
        header("location: manage-appointments.php?action=error&message=".urlencode("Action failed: " . $e->getMessage()));
        exit();
    }

} else {
    header("location: manage-appointments.php?action=error&message=".urlencode("Invalid request."));
    exit();
}
?>