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

        
    <title>SafeSpace PH | Login</title>

    
    
</head>
<body>
    <?php

    //learn from w3schools.com
    //Unset all the server side variables

    session_start();

    $_SESSION["user"]="";
    $_SESSION["usertype"]="";
    
    // Set the new timezone
    date_default_timezone_set('Asia/Kolkata');
    $date = date('Y-m-d');

    $_SESSION["date"]=$date;
    

    //import database
    include("connection.php");

    



    if($_POST){

        $email=$_POST['useremail'];
        $password=$_POST['userpassword'];
        
        $error = ''; // Only set error if needed

        $result= $database->query("select * from webuser where email='$email'");
        if($result->num_rows==1){
            $utype=$result->fetch_assoc()['usertype'];
            if ($utype == 'c') {
                $checker = $database->query("SELECT * FROM client WHERE cemail='$email' AND cpassword='$password'");
                if ($checker->num_rows == 1) {
                    $_SESSION['user'] = $email;
                    $_SESSION['usertype'] = 'c';
                    header('location: client/index.php');
                } else {
                    $error = '<label for="promter" class="form-label">Wrong credentials: Invalid email or password</label>';
                }

            }elseif($utype=='a'){
                $checker = $database->query("select * from admin where aemail='$email' and apassword='$password'");
                if ($checker->num_rows==1){


                    //   Admin dashbord
                    $_SESSION['user']=$email;
                    $_SESSION['usertype']='a';
                    
                    header('location: admin/index.php');

                }else{
                    $error='<label for="promter" class="form-label">Wrong credentials: Invalid email or password</label>';
                }


            } elseif ($utype == 'l') {
                $checker = $database->query("SELECT * FROM lawyer WHERE lawyeremail='$email' AND lawyerpassword='$password'");
                if ($checker->num_rows == 1) {
                    $_SESSION['user'] = $email;
                    $_SESSION['usertype'] = 'l';
                    header('location: lawyer/index.php');
                } else {
                    $error = '<label for="promter" class="form-label">Wrong credentials: Invalid email or password</label>';
                }
            }
            
        }else{
            $error='<label for="promter" class="form-label">We cannot found any account for this email.</label>';
        }

    } else {
        $error = ''; // No error by default
    }

    ?>





    <center>
    <div class="container">
        <table border="0" style="margin: 0;padding: 0;width: 60%;">
            <tr>
                <td>
                    <p class="header-text">Welcome Back!</p>
                </td>
            </tr>
        <div class="form-body">
            <tr>
                <td>
                    <p class="sub-text">Login with your details to continue</p>
                </td>
            </tr>
            <tr>
                <form action="" method="POST" >
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
                <td><br>
                <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo $error ?>
                </div>
                <?php endif; ?>
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
        </div>
            <tr>
                <td>
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280;">Don't have an account&#63; </label>
                    <a href="signup.php" class="hover-link1 non-style-link">Sign Up</a>
                    <br><br><br>
                </td>
            </tr>
                        
                        
    
                        
                    </form>
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