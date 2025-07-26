<?php

session_start(); 

// Set the default timezone
date_default_timezone_set('Asia/Manila'); 

include("connection.php");

// ... the rest of your code

if($_POST){

    $result= $database->query("select * from webuser");

    $fname=$_SESSION['personal']['fname'];
    $lname=$_SESSION['personal']['lname'];
    $name=$fname." ".$lname;
    $address=$_SESSION['personal']['address'];
    $dob=$_SESSION['personal']['dob'];
    $email=$_POST['newemail'];
    $tele=$_POST['tele']; 
    $newpassword=$_POST['newpassword'];
    $cpassword=$_POST['cpassword'];
    
    $password_valid = true;
    if (strlen($newpassword) < 8) {
        $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password must be at least 8 characters long.</label>';
        $password_valid = false;
    } elseif (!preg_match('/[A-Z]/', $newpassword)) {
        $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password must contain at least one uppercase letter.</label>';
        $password_valid = false;
    } elseif (!preg_match('/[a-z]/', $newpassword)) {
        $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password must contain at least one lowercase letter.</label>';
        $password_valid = false;
    } elseif (!preg_match('/[0-9]/', $newpassword)) {
        $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password must contain at least one number.</label>';
        $password_valid = false;
    } elseif (!preg_match('/[^A-Za-z0-9]/', $newpassword)) {
        $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password must contain at least one special character.</label>';
        $password_valid = false;
    }

    if ($password_valid) {
        if ($newpassword == $cpassword){
            $result= $database->query("select * from webuser where email='$email';");
            if($result->num_rows==1){
                $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Already have an account for this Email address.</label>';
            }else{
               

                $database->query("insert into client(cemail,cname,cpassword, caddress,cdob,ctel) values('$email','$name','$newpassword','$address','$dob','$tele');");
                $database->query("insert into webuser values('$email','u')");

                require 'send_email.php'; 

        
                sendConfirmationEmail($email, $name);

                $_SESSION['user'] = $email;
                $_SESSION['usertype'] = 'u'; // unverified user
                // Get the new user's cid from the database
                $get_cid = $database->query("SELECT cid, cname FROM client WHERE cemail='$email' LIMIT 1");
                if ($get_cid && $get_cid->num_rows == 1) {
                    $client_data = $get_cid->fetch_assoc();
                    $_SESSION['cid'] = $client_data['cid'];
                    $_SESSION['cname'] = $client_data['cname'];
                }
                header('Location: client/index_unverified.php');
                $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;"></label>';
            }
            
        }else{
            $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password Conformation Error! Reconform Password</label>';
        }
    }

    
}else{
  
    $error='<label for="promter" class="form-label"></label>';
}

?>
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
        
    <title>Create Account | SafeSpace PH</title>
    <style>
        .container{
            animation: transitionIn-X 0.5s;
        }
        .phone-input-container {
            display: flex;
            align-items: center;
            padding: 0; 
            overflow: hidden; 
        }
       
        .phone-input-container.input-text {
          
            padding: 0; 
        }

        .phone-input-prefix {
            font-size: inherit; 
            color: inherit;
            
            border-right: 1px solid var(--primarycolor); 
            
            padding: 10px; 
            white-space: nowrap;
            display: flex;
            align-items: center;
            height: 100%;
            box-sizing: border-box; 
        }
        .phone-input-field {
            border: none; 
            flex-grow: 1;
            font-size: inherit; 
            color: inherit;
            
            padding: 10px; 
            background-color: transparent; 
            outline: none; 
        }
        .phone-input-container.input-text:focus-within {
            border-color: var(--activecolor);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }
        .password-requirements {
            font-size: 12px;
            color: #777; 
            text-align: left;
            margin-top: 5px;
            margin-bottom: 15px;
        }
        .password-requirements li {
            list-style: none; 
            margin-left: 0;
            padding-left: 0;
        }
        .password-requirements .error-red {
            color: rgb(255, 62, 62); 
        }
        .password-requirements .success-green {
            color: green; 
        }
        .validation-feedback {
            font-size: 12px;
            text-align: left;
            margin-top: 5px;
            margin-bottom: 10px;
        }
        .validation-feedback.error {
            color: rgb(255, 62, 62);
        }
        .validation-feedback.success {
            color: green;
        }
    </style>
</head>
<body>


    <center>
    <div class="container">
        <table border="0" style="width: 69%;">
            <tr>
                 <td colspan="2">
                    <img src="img/logo.png" alt="SafeSpace PH Logo" class="logo" style="width: 70px; height: 70px; margin-top: 30px;">
                    <p class="header-text" style="margin-top: 0;">Sign Up</p>
                    <p class="sub-text">Add Your Personal Details to Continue</p>
                </td>
            </tr>
            <tr>
                <form action="" method="POST" onsubmit="return validatePassword()">
                <td class="label-td" colspan="2">
                    <label for="newemail" class="form-label">Email: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="email" name="newemail" class="input-text" placeholder="Email Address" required>
                </td>
                
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="tele" class="form-label">Mobile Number: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <div class="phone-input-container input-text">
                        <span class="phone-input-prefix">+63</span>
<input type="tel" name="tele" class="phone-input-field" placeholder="e.g., 9123456789" pattern="9[0-9]{9}" maxlength="10" required>                    </div>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="newpassword" class="form-label">Create New Password: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="password" name="newpassword" id="newpassword" class="input-text" placeholder="New Password" required onkeyup="checkPasswordStrength()">
                    <ul class="password-requirements">
                        <li id="len_req">&#x2022; At least 8 characters long</li>
                        <li id="upper_req">&#x2022; At least one uppercase letter (A-Z)</li>
                        <li id="lower_req">&#x2022; At least one lowercase letter (a-z)</li>
                        <li id="num_req">&#x2022; At least one number (0-9)</li>
                        <li id="special_req">&#x2022; At least one special character (!@#$%^&*)</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="cpassword" class="form-label">Confirm Password: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="password" name="cpassword" id="cpassword" class="input-text" placeholder="Confirm Password" required onkeyup="validatePasswordMatch()">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div id="password-feedback" class="validation-feedback"></div>
                    <div id="cpassword-feedback" class="validation-feedback"></div>
                </td>
            </tr>
     
            <tr>
                
                <td colspan="2">
                    <?php echo $error ?>

                </td>
            </tr>
            
<tr>
    <td>
        <input type="reset" value="Reset" class="login-btn btn-primary-soft btn" style="width: 100%;">
    </td>
    <td>
        <input type="submit" value="Sign Up" class="login-btn btn-primary btn" style="width: 100%;">
    </td>
</tr>
<tr>
    <td colspan="2">
        <button type="button" onclick="window.location.href='signup.php';" class="back-btn btn btn-primary-soft" style="width: 100%;">
            Back
        </button>
    </td>
</tr>

            <tr>
                <td colspan="2">
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280;">Already have an account&#63; </label>
                    <a href="login.php" class="hover-link1 non-style-link">Login</a>
                    <br><br><br>
                </td>
            </tr>

                    </form>
            </tr>
        </table>

    </div>
</center>
<script>
    function checkPasswordStrength() {
        var password = document.getElementById("newpassword").value;
        var feedback = document.getElementById("password-feedback");

        var lenReq = document.getElementById("len_req");
        var upperReq = document.getElementById("upper_req");
        var lowerReq = document.getElementById("lower_req");
        var numReq = document.getElementById("num_req");
        var specialReq = document.getElementById("special_req");

        var allConditionsMet = true;

        function updateRequirementStatus(element, condition) {
            if (condition) {
                element.classList.add("success-green");
                element.classList.remove("error-red");
            } else {
                element.classList.add("error-red");
                element.classList.remove("success-green");
                allConditionsMet = false; 
            }
        }

        updateRequirementStatus(lenReq, password.length >= 8);

        updateRequirementStatus(upperReq, /[A-Z]/.test(password));

        updateRequirementStatus(lowerReq, /[a-z]/.test(password));

        updateRequirementStatus(numReq, /[0-9]/.test(password));

        updateRequirementStatus(specialReq, /[^A-Za-z0-9]/.test(password));

        if (allConditionsMet && password.length > 0) { 
            feedback.className = "validation-feedback success";
            feedback.innerHTML = "Password strength: Strong";
        } else if (password.length === 0) {
            feedback.innerHTML = ""; 
            feedback.className = "validation-feedback";
        }
        else {
            feedback.className = "validation-feedback error";
            feedback.innerHTML = "Please meet all password requirements.";
        }
        
        validatePasswordMatch(); 
    }

    function validatePasswordMatch() {
        var password = document.getElementById("newpassword").value;
        var confirmPassword = document.getElementById("cpassword").value;
        var feedback = document.getElementById("cpassword-feedback");

        if (confirmPassword === "") {
            feedback.innerHTML = ""; 
            feedback.className = "validation-feedback";
        } else if (password !== confirmPassword) {
            feedback.className = "validation-feedback error";
            feedback.innerHTML = "Passwords do not match.";
        } else {
            feedback.className = "validation-feedback success";
            feedback.innerHTML = "Passwords match.";
        }
    }

    function validatePassword() {
        var password = document.getElementById("newpassword").value;
        var confirmPassword = document.getElementById("cpassword").value;
        var isValid = true;
        var feedback = document.getElementById("password-feedback");
        var cfeedback = document.getElementById("cpassword-feedback");

        checkPasswordStrength();
       
        var lenMet = document.getElementById("len_req").classList.contains("success-green");
        var upperMet = document.getElementById("upper_req").classList.contains("success-green");
        var lowerMet = document.getElementById("lower_req").classList.contains("success-green");
        var numMet = document.getElementById("num_req").classList.contains("success-green");
        var specialMet = document.getElementById("special_req").classList.contains("success-green");

        if (!(lenMet && upperMet && lowerMet && numMet && specialMet)) {
            isValid = false;
        }

        validatePasswordMatch();
        if (cfeedback.classList.contains("error")) {
            isValid = false;
        }

        return isValid;
    }

</script>
</body>
</html>