<?php
    session_start();

    include("../connection.php");

    if($_POST){
        $name = $_POST['name'];
        $oldemail = $_POST["oldemail"];
        $rollid = $_POST['nic']; 
        $spec = $_POST['spec'];
        $email = $_POST['email'];
        $tele = $_POST['Tele'];
        $password = $_POST['password'];
        $cpassword = $_POST['cpassword'];
        $id = $_POST['id00'];
        
        if ($password == $cpassword){
            $error = '3';
            
            $email_check_result = $database->query("SELECT lawyerid FROM lawyer WHERE lawyeremail='$email';");

            if($email_check_result->num_rows > 0){
                $id2 = $email_check_result->fetch_assoc()["lawyerid"];
            } else {
                $id2 = $id;
            }
            
            if($id2 != $id){
                $error = '1'; 
            } else {
                $sql1 = "UPDATE lawyer SET lawyeremail='$email', lawyername='$name', lawyerrollid='$rollid', lawyertel='$tele', specialties=$spec";
                
                if (!empty($password)) {
                    $sql1 .= ", lawyerpassword='$password'";
                }
                
                $sql1 .= " WHERE lawyerid=$id;";
                
                $database->query($sql1);

                $sql2 = "UPDATE webuser SET email='$email' WHERE email='$oldemail';";
                $database->query($sql2);
                
                $_SESSION["user"] = $email;

                $error = '4'; 
            }
        } else {
            $error = '2'; 
        }
    } else {
        $error = '3';
    }
    
    header("location: settings.php?action=edit&error=".$error."&id=".$id);
    exit(); 
?>