<?php
// This PHP block MUST be at the very top of the file, before any HTML or whitespace.

//learn from w3schools.com
//Unset all the server side variables

session_start(); // This line must be the first thing executed.

$_SESSION["user"]="";
$_SESSION["usertype"]="";

// Set the new timezone
date_default_timezone_set('Asia/Manila'); // Changed timezone to Asia/Manila for Philippines
$date = date('Y-m-d');

$_SESSION["date"]=$date;


//import database
include("connection.php");




if($_POST){

    $result= $database->query("select * from webuser");

    $fname=$_SESSION['personal']['fname'];
    $lname=$_SESSION['personal']['lname'];
    $name=$fname." ".$lname;
    $address=$_SESSION['personal']['address'];
    $dob=$_SESSION['personal']['dob'];
    $email=$_POST['newemail'];
    $tele=$_POST['tele']; // This will now only contain the 10 digits
    $newpassword=$_POST['newpassword'];
    $cpassword=$_POST['cpassword'];
    
    // Server-side validation for password
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
                // Prepend +63 to the phone number before saving to the database if needed
                // If your database stores the full number with +63, uncomment the line below
                // $full_tele = "+63" . $tele; 
                // Otherwise, just use $tele if the database only stores the 10 digits

                $database->query("insert into client(cemail,cname,cpassword, caddress,cdob,ctel) values('$email','$name','$newpassword','$address','$dob','$tele');");
                $database->query("insert into webuser values('$email','c')");

                require 'send_email.php'; // make sure this path is correct

                // Call the function with appropriate parameters
                sendConfirmationEmail($email, $name);

                header('Location: client/index.php');
                $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;"></label>';
            }
            
        }else{
            $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password Conformation Error! Reconform Password</label>';
        }
    }

    
}else{
    //header('location: signup.php');
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
    <link rel="icon" type="image/png" href="https://i.ibb.co/qYYZs46L/logo.png">
        
    <title>Create Account | SafeSpace PH</title>
    <style>
        .container{
            animation: transitionIn-X 0.5s;
        }
        /* Custom styling for the phone number input to match the design */
        .phone-input-container {
            display: flex;
            align-items: center;
            /* The .input-text class will provide the base styling for border, background, etc. */
            padding: 0; /* Remove padding from the container as children will manage it */
            overflow: hidden; /* Ensures border-radius is applied correctly to children */
        }
        /* Apply the base input-text styles to the container */
        /* This ensures the phone input container looks exactly like other input fields */
        .phone-input-container.input-text {
            /* The styles from .input-text (defined in main.css/signup.css) will apply here */
            /* This includes border, border-radius, background-color, width, height, font-size, color */
            /* We only override padding to manage internal spacing with prefix and field */
            padding: 0; 
        }

        .phone-input-prefix {
            /* Inherit font size and color from the parent .phone-input-container.input-text */
            font-size: inherit; 
            color: inherit;
            
            /* Add the separator line */
            border-right: 1px solid var(--primarycolor); /* Use a variable or specific color for the separator */
            
            /* Adjust padding to align text vertically and create space for the separator */
            padding: 10px; /* Adjust as needed to match vertical padding of other inputs */
            white-space: nowrap; /* Prevent +63 from wrapping */
            display: flex; /* Use flex to vertically center the text within the prefix */
            align-items: center;
            height: 100%; /* Make prefix take full height of container */
            box-sizing: border-box; /* Include padding and border in the element's total width and height */
        }
        .phone-input-field {
            border: none; /* Remove individual input border */
            flex-grow: 1; /* Allow input to take remaining space */
            /* Inherit font size and color from the parent .phone-input-container.input-text */
            font-size: inherit; 
            color: inherit;
            
            /* Adjust padding to align text vertically */
            padding: 10px; /* Adjust as needed to match vertical padding of other inputs */
            background-color: transparent; /* Transparent background */
            outline: none; /* Remove outline on focus */
        }
        /* Ensure the phone input container also gets the focus style if .input-text has it */
        .phone-input-container.input-text:focus-within {
            border-color: var(--activecolor); /* Assuming --activecolor for focus state */
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25); /* Example shadow for focus */
        }
        .password-requirements {
            font-size: 12px;
            color: #777; /* Default color for requirements */
            text-align: left;
            margin-top: 5px;
            margin-bottom: 15px;
        }
        .password-requirements li {
            list-style: none; /* Remove bullet points */
            margin-left: 0;
            padding-left: 0;
        }
        .password-requirements .error-red {
            color: rgb(255, 62, 62); /* Red for unmet requirements */
        }
        .password-requirements .success-green {
            color: green; /* Green for met requirements */
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
                    <p class="header-text">Let's Get Started</p>
                    <p class="sub-text">It's Okey, Now Create User Account.</p>
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
                        <input type="tel" name="tele" class="phone-input-field" placeholder="e.g., 9123456789" pattern="[9]{1}[0-9]{9}" maxlength="10" required>
                    </div>
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

        // Helper function to update class
        function updateRequirementStatus(element, condition) {
            if (condition) {
                element.classList.add("success-green");
                element.classList.remove("error-red");
            } else {
                element.classList.add("error-red");
                element.classList.remove("success-green");
                allConditionsMet = false; // Only set to false if condition is NOT met
            }
        }

        // Length check
        updateRequirementStatus(lenReq, password.length >= 8);

        // Uppercase check
        updateRequirementStatus(upperReq, /[A-Z]/.test(password));

        // Lowercase check
        updateRequirementStatus(lowerReq, /[a-z]/.test(password));

        // Number check
        updateRequirementStatus(numReq, /[0-9]/.test(password));

        // Special character check
        updateRequirementStatus(specialReq, /[^A-Za-z0-9]/.test(password));

        if (allConditionsMet && password.length > 0) { // Add password.length > 0 to prevent "Strong" for empty
            feedback.className = "validation-feedback success";
            feedback.innerHTML = "Password strength: Strong";
        } else if (password.length === 0) {
            feedback.innerHTML = ""; // Clear feedback if password is empty
            feedback.className = "validation-feedback";
        }
        else {
            feedback.className = "validation-feedback error";
            feedback.innerHTML = "Please meet all password requirements.";
        }
        
        validatePasswordMatch(); // Also check if passwords match when new password changes
    }

    function validatePasswordMatch() {
        var password = document.getElementById("newpassword").value;
        var confirmPassword = document.getElementById("cpassword").value;
        var feedback = document.getElementById("cpassword-feedback");

        if (confirmPassword === "") {
            feedback.innerHTML = ""; // Clear feedback if confirm password is empty
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

        // Perform password strength check
        checkPasswordStrength();
        // The checkPasswordStrength function now updates `allConditionsMet`
        // We need to re-evaluate it based on the current state of the list items
        var lenMet = document.getElementById("len_req").classList.contains("success-green");
        var upperMet = document.getElementById("upper_req").classList.contains("success-green");
        var lowerMet = document.getElementById("lower_req").classList.contains("success-green");
        var numMet = document.getElementById("num_req").classList.contains("success-green");
        var specialMet = document.getElementById("special_req").classList.contains("success-green");

        if (!(lenMet && upperMet && lowerMet && numMet && specialMet)) {
            isValid = false;
        }

        // Perform password match check
        validatePasswordMatch();
        if (cfeedback.classList.contains("error")) {
            isValid = false;
        }

        return isValid;
    }

    // Call checkPasswordStrength on page load to set initial state if needed
    // (e.g., if there's a pre-filled password, though typically not for new accounts)
    // window.onload = checkPasswordStrength; // Uncomment if you want immediate validation on load
</script>
</body>
</html>