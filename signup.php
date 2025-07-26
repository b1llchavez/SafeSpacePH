<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/signup.css">
    <link rel="icon" type="image/png" href="img/logo.png">
    <title>Sign Up | SafeSpace PH</title>
    
    <style>
        /* --- LAYOUT REFINEMENTS --- */

        /* Ensure the body takes up the full viewport height */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden; /* Prevent scrolling on the main page */
        }

        /* Center the login form vertically and horizontally */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #F6F7FA; /* Match background from login.css */
        }
    </style>

</head>
<body>
<?php

session_start();

$_SESSION["user"]="";
$_SESSION["usertype"]="";

//Set the session
date_default_timezone_set('Asia/Kolkata');
$date = date('Y-m-d');

$_SESSION["date"]=$date;

if($_POST){

    $_SESSION["personal"]=array(
        'fname'=>$_POST['fname'],
        'lname'=>$_POST['lname'],
        'address'=>$_POST['address'],
        'dob'=>$_POST['dob']
    );

    print_r($_SESSION["personal"]);
    header("location: create-account.php");
}

?>

    <div class="container">
        <table border="0" style="margin: 0;padding: 0;width: 60%; margin: auto;">
            <tr>
                <td style="display: flex; justify-content: center; align-items: center; padding-bottom: 0px; padding-top: 20px;">
                    <img src="img/logo.png" alt="SafeSpace PH Logo" style="width: 90px; height: auto; margin-right: 15px;">
                    <p class="header-text" style="margin: 0;">Sign Up</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="sub-text">Add Your Personal Details to Continue</p>
                </td>
            </tr>
            <form action="" method="POST" >
            <tr>
                <td class="label-td" colspan="2">
                    <label for="name" class="form-label">Name: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <input type="text" name="fname" class="input-text" placeholder="First Name" required>
                </td>
                <td class="label-td">
                    <input type="text" name="lname" class="input-text" placeholder="Last Name" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="address" class="form-label">Address: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="text" name="address" class="input-text" placeholder="Address" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="dob" class="form-label">Date of Birth: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="date" name="dob" class="input-text" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                </td>
            </tr>

            <tr>
                 <td style="padding-right: 5px;">
                    <input type="reset" value="Reset" class="login-btn btn-primary-soft btn" >
                </td>
                <td style="padding-left: 5px;">
                    <input type="submit" value="Next" class="login-btn btn-primary btn">
                </td>
            </tr>
            </form>
            <tr>
                <td colspan="2">
                    <button type="button" onclick="window.location.href='index.html';" class="back-btn btn-primary-soft btn">
                        Return Home
                    </button>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <br>
                    <label for="" class="sub-text">Already have an account? </label>
                    <a href="login.php" class="hover-link1 non-style-link">Login</a>
                    <br><br><br>
                </td>
            </tr>
        </table>
    </div>

<style>
/* --- VISUAL IMPROVEMENTS & STYLE SYNC --- */

/* Match container style with admin modals */
.container {
    border-radius: 16px !important;
    border: 1px solid #e0e0e0 !important;
}

/* More welcoming sub-text */
.sub-text {
  font-size: 16px !important;
  color: rgb(108, 117, 125) !important;
  margin-bottom: 25px;
}

/* Input field style to match theme */
.input-text:focus {
    border-color: #5A2675 !important;
    box-shadow: 0 0 0 2px #e2d8fa !important; /* Soft purple glow */
}

/* Base button style to match admin modal buttons */
.btn {
    border: none;
    border-radius: 7px;
    padding: 12px 28px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s, box-shadow 0.2s;
    width: 100%;
}

/* Primary "Login" button style */
.btn-primary {
    background-color: #5A2675;
    color: white;
    margin-bottom: 10px; /* Spacing between buttons */
}
.btn-primary:hover {
    background-color: #4a2061; /* Darker purple on hover */
}

/* Soft "Return Home" button style */
.btn-primary-soft {
    background: #f0e9f7;
    color: #5A2675;
}
.btn-primary-soft:hover {
    background-color: #9D72B3; /* More visible lavender on hover */
}
</style>
</body>
</html>