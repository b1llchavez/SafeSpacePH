<?php

    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }

    }else{
        header("location: ../login.php");
    }
    
    
    if($_GET){

        include("../connection.php");
        $id=$_GET["id"];
        $result001= $database->query("select * from lawyer where lawyerid=$id;");
        $email=($result001->fetch_assoc())["lawyeremail"];
        $sql= $database->query("delete from webuser where email='$email';");
        $sql= $database->query("delete from lawyer where lawyeremail='$email';");

        header("location: lawyers.php");
    }


?>