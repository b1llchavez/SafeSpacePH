<?php

    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='c'){
            header("location: ../login.php");
        }else{
            $useremail=$_SESSION["user"];
        }

    }else{
        header("location: ../login.php");
    }
    


    include("../connection.php");
    $userrow = $database->query("select * from client where cemail='$useremail'");
    $userfetch=$userrow->fetch_assoc();
    $userid= $userfetch["cid"];
    $username=$userfetch["cname"];

    
    if($_GET){

        include("../connection.php");
        $id=$_GET["id"];
        $result001= $database->query("select * from client where cid=$id;");
        $email=($result001->fetch_assoc())["cemail"];
        $sql= $database->query("delete from webuser where email='$email';");
        $sql= $database->query("delete from client where cemail='$email';");

        header("location: ../logout.php");
    }


?>