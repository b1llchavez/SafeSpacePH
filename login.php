<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" type="image/png" href="https://i.ibb.co/qYYZs46L/logo.png">
        
    <title>Login | SafeSpace PH</title>
</head>
<body>
    <?php

    session_start();

    // REMOVED: $_SESSION["user"]="";
    // REMOVED: $_SESSION["usertype"]="";
    // Reason: These lines were unsetting session variables immediately after starting the session,
    // which would prevent successful logins from persisting.

    // Set the new timezone (already present, just ensured it's here)
    date_default_timezone_set('Asia/Manila'); // Changed from Asia/Kolkata to Asia/Manila for Quezon City, Philippines location.
    $date = date('Y-m-d');

    $_SESSION["date"]=$date; // This stores the current date in session

    //import database
    include("connection.php");

    // Check if the form was submitted via POST
    if($_POST){
        $email = $_POST['useremail']; // Assuming 'useremail' is the name attribute for the email input
        $password = $_POST['userpassword']; // Assuming 'userpassword' is the name attribute for the password input
        
        // Sanitize inputs
        $email = mysqli_real_escape_string($database, $email);
        // Note: Password hashing should be implemented for security.
        // For now, we are using plain text password as per your existing structure.
        $password = mysqli_real_escape_string($database, $password); 

        $error = ""; // Initialize error message

        // First, check the webuser table to determine the usertype
        $webuser_query = $database->query("SELECT * FROM webuser WHERE email='$email'");

        if ($webuser_query->num_rows == 1) {
            $webuser_data = $webuser_query->fetch_assoc();
            $usertype = $webuser_data['usertype'];

            // Authenticate based on usertype
            if ($usertype == 'a') {
                $auth_query = $database->query("SELECT * FROM admin WHERE aemail='$email' AND apassword='$password'");
                if ($auth_query->num_rows == 1) {
                    $_SESSION['user'] = $email;
                    $_SESSION['usertype'] = 'a';
                    header('location: admin/index.php');
                    exit();
                } else {
                    $error = 'Wrong credentials: Invalid email or password';
                }
            } elseif ($usertype == 'c') {
                $auth_query = $database->query("SELECT * FROM client WHERE cemail='$email' AND cpassword='$password'");
                if ($auth_query->num_rows == 1) {
                    $client_data = $auth_query->fetch_assoc();
                    $_SESSION['user'] = $email;
                    $_SESSION['usertype'] = 'c';
                    $_SESSION['cid'] = $client_data['cid']; // Store client ID
                    $_SESSION['cname'] = $client_data['cname']; // Store client name for display in client pages
                    header('location: client/index.php'); // Redirect to client dashboard
                    exit();
                } else {
                    $error = 'Wrong credentials: Invalid email or password';
                }
            } elseif ($usertype == 'l') {
                $auth_query = $database->query("SELECT * FROM lawyer WHERE lawyeremail='$email' AND lawyerpassword='$password'");
                if ($auth_query->num_rows == 1) {
                    $lawyer_data = $auth_query->fetch_assoc();
                    $_SESSION['user'] = $email;
                    $_SESSION['usertype'] = 'l';
                    $_SESSION['lawyerid'] = $lawyer_data['lawyerid']; // Store lawyer ID
                    $_SESSION['lawyername'] = $lawyer_data['lawyername']; // Store lawyer name for display in lawyer pages
                    header('location: lawyer/index.php'); // Redirect to lawyer dashboard (to be created)
                    exit();
                } else {
                   $error = 'Wrong credentials: Invalid email or password';;
                }
            }
        } else {
            $error = 'Wrong credentials: Invalid email or password';
        }

        // Set the error message in session if redirection hasn't happened
        if (!empty($error)) {
            $_SESSION['login_error'] = $error;
            header('location: login.php?error=' . urlencode($error)); // Redirect with error in URL for display
            exit();
        }

    } else {
        // If someone tries to access login.php directly without POST request,
        // and they are already logged in, redirect them to their respective dashboard.
        if(isset($_SESSION['usertype'])) {
            if($_SESSION['usertype'] == 'a') {
                header('location: admin/index.php');
                exit();
            } elseif($_SESSION['usertype'] == 'c') {
                header('location: client/client_dashboard.php');
                exit();
            } elseif($_SESSION['usertype'] == 'l') {
                header('location: lawyer/lawyer_dashboard.php');
                exit();
            }
        }
    }
    ?>

    <center>
        <div class="container">
            <table border="0" style="margin: 0;padding: 0;width: 60%;">
                <tr>
                    <td>
                        <p class="header-text">Login</p>
                    </td>
                </tr>
                <div class="form-body">
                    <tr>
                        <td>
                            <p class="sub-text">Login with your details to continue</p>
                        </td>
                    </tr>
                    <tr>
                        <form action="" method="POST">
                        <td class="label-td">
                            <label for="useremail" class="form-label">Email: </label>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td">
                            <input type="email" name="useremail" class="input-text" placeholder="Email Address" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td">
                            <label for="userpassword" class="form-label">Password: </label>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td">
                            <input type="Password" name="userpassword" class="input-text" placeholder="Password" required>
                        </td>
                    </tr>
                    <tr>
                        <td><br/>
                        <?php 
                                // Display error message from URL parameter
                                if(isset($_GET["error"])){
                                     echo '<div class="error-message"><label>' . htmlspecialchars(urldecode($_GET["error"])) . '</label></div>';
                                }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="submit" value="Login" class="login-btn btn-primary btn">
                        </td>
                    </tr>
                    <tr>
                <td>
                    <button type="button" onclick="window.location.href='index.html';" class="back-btn btn btn-primary-soft">
                        Back
                    </button>
                </td>
            </tr>
                    </form>
                    <tr>
                        <td>
                            <br>
                            <label for="" class="sub-text">Don't have an account? </label>
                            <a href="signup.php" class="hover-link1 non-style-link">Sign Up</a>
                            <br><br><br>
                        </td>
                    </tr>
                </div>
            </table>
        </div>
    </center>
    <style>
.login-btn {
  width: 100%;
}

.back-btn {
  width: 100%;
}

.error-message label {
  display: block;
  background: #ffeaea;
  color: #b71c1c;
  border: 1.5px solid #f5c2c7;
  border-radius: 6px;
  padding: 12px 14px;
  margin: 0 auto 10px auto;
  font-size: 16px;
  text-align: center;
  font-weight: 600;
  max-width: 100%;
  box-sizing: border-box;
  letter-spacing: 0.02em;
  box-shadow: 0 2px 8px rgba(183,28,28,0.07);
  animation: shake 0.2s 1;
}
@keyframes shake {
  0% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  50% { transform: translateX(4px); }
  75% { transform: translateX(-4px); }
  100% { transform: translateX(0); }
}
</style>
</body>
</html>