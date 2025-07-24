<?php
    session_start(); // Add this to manage session variables

    //import database
    include("../connection.php");

    if($_POST){
        // Get all the submitted data from the form
        $name = $_POST['name'];
        $oldemail = $_POST["oldemail"];
        $rollid = $_POST['nic']; 
        $spec = $_POST['spec'];
        $email = $_POST['email'];
        $tele = $_POST['Tele'];
        $password = $_POST['password'];
        $cpassword = $_POST['cpassword'];
        $id = $_POST['id00'];
        
        // Check if passwords match
        if ($password == $cpassword){
            $error = '3'; // Default error
            
            // Check if the new email address is already being used by ANOTHER lawyer
            $email_check_result = $database->query("SELECT lawyerid FROM lawyer WHERE lawyeremail='$email';");

            if($email_check_result->num_rows > 0){
                $id2 = $email_check_result->fetch_assoc()["lawyerid"];
            } else {
                $id2 = $id;
            }
            
            if($id2 != $id){
                $error = '1'; // Error: Email is already taken
            } else {
                // Construct the base of the update query with correct column names
                $sql1 = "UPDATE lawyer SET lawyeremail='$email', lawyername='$name', lawyerrollid='$rollid', lawyertel='$tele', specialties=$spec";
                
                if (!empty($password)) {
                    $sql1 .= ", lawyerpassword='$password'";
                }
                
                $sql1 .= " WHERE lawyerid=$id;";
                
                $database->query($sql1);

                $sql2 = "UPDATE webuser SET email='$email' WHERE email='$oldemail';";
                $database->query($sql2);
                
                // FIX: Update the session with the new email address
                $_SESSION["user"] = $email;

                $error = '4'; // Success
            }
        } else {
            $error = '2'; // Error: Passwords do not match
        }
    } else {
        $error = '3'; // Error: Not a POST request
    }
    
    // Redirect back to the settings page, passing the error code and ID
    header("location: settings.php?action=edit&error=".$error."&id=".$id);
    exit(); 
?>