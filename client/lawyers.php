<?php
    // Start session and include connection at the very top
    session_start();
    include("../connection.php");

    // Check user session and type
    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='c'){
            header("location: ../login.php");
            exit(); // Use exit() after header to stop script execution
        }else{
            $useremail=$_SESSION["user"];
        }
    }else{
        header("location: ../login.php");
        exit();
    }

    // Fetch client details
    $userrow = $database->query("select * from client where cemail='$useremail'");
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch["cid"];
    $username = $userfetch["cname"];

    // Set timezone
    date_default_timezone_set('Asia/Manila');
    $today = date('Y-m-d');
    
    // This variable will hold the HTML for any modal to be displayed
    $modal_html = "";

    // Handle GET actions for modals
    if(isset($_GET["action"]) && isset($_GET["id"])){
        $id = $_GET["id"];
        $action = $_GET["action"];

        if($action=='view'){
            $sqlmain= "select * from lawyer where lawyerid='$id'";
            $result= $database->query($sqlmain);
            $row=$result->fetch_assoc();
            $name=$row["lawyername"];
            $email=$row["lawyeremail"];
            $spe=$row["specialties"];
            $spcil_res= $database->query("select sname from specialties where id='$spe'");
            $spcil_array= $spcil_res->fetch_assoc();
            $spcil_name = $spcil_array ? $spcil_array["sname"] : "N/A";
            $tele=$row['lawyertel'];
            
            $modal_html = '
            <div id="popup1" class="overlay active">
                <div class="modal-content">
                    <a href="lawyers.php" class="close-btn">&times;</a>
                    <h2 class="modal-header">Lawyer Details</h2>
                    <div class="modal-divider"></div>
                    <div class="modal-body">
                        <div class="detail-grid">
                            <div class="detail-item detail-full"><strong>Name:</strong> <span>' . htmlspecialchars($name) . '</span></div>
                            <div class="detail-item detail-full"><strong>Email:</strong> <span>' . htmlspecialchars($email) . '</span></div>
                            <div class="detail-item"><strong>Telephone:</strong> <span>' . htmlspecialchars($tele) . '</span></div>
                            <div class="detail-item"><strong>Specialties:</strong> <span>' . htmlspecialchars($spcil_name) . '</span></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="lawyers.php" class="modal-btn modal-btn-soft">Close</a>
                    </div>
                </div>
            </div>';

        } elseif($action=='session'){
            $name = $_GET["name"];
            $sql_sessions = "SELECT * FROM schedule WHERE lawyerid='$id' AND scheduledate >= '$today' ORDER BY scheduledate ASC, scheduletime ASC";
            $result_sessions = $database->query($sql_sessions);
            
            $sessions_content = '';
            if ($result_sessions->num_rows > 0) {
                $sessions_content .= '<div style="max-height: 300px; overflow-y: auto; padding-right: 15px;">';
                while($row_session = $result_sessions->fetch_assoc()){
                    $title = $row_session["title"];
                    $scheduledate = date("F j, Y", strtotime($row_session["scheduledate"]));
                    $scheduletime = date("g:i A", strtotime($row_session["scheduletime"]));
                    
                    // MODIFIED: Removed the "Book Now" button and adjusted styling
                    $sessions_content .= '
                    <div style="background-color: #f8f9fa; border-radius: 8px; padding: 15px; margin-bottom: 12px;">
                        <div>
                            <div style="font-weight: 600; color: #391053;">'.htmlspecialchars($title).'</div>
                            <div style="font-size: 14px; color: #555;">'.$scheduledate.' at '.$scheduletime.'</div>
                        </div>
                    </div>';
                }
                $sessions_content .= '</div>';
            } else {
                $sessions_content = '<p style="text-align:center; color: #555; font-size: 16px;">No upcoming sessions found for this lawyer.</p>';
            }

            $modal_html = '
            <div id="popup1" class="overlay active">
                <div class="modal-content">
                    <a href="lawyers.php" class="close-btn">&times;</a>
                    <h2 class="modal-header">Upcoming Sessions</h2>
                    <p style="text-align:center; margin-top:-10px; margin-bottom: 20px; color: #5A2675; font-weight: 600;">' . htmlspecialchars($name) . '</p>
                    <div class="modal-divider"></div>
                    <div class="modal-body">
                        ' . $sessions_content . '
                    </div>
                    <div class="modal-footer">
                        <a href="lawyers.php" class="modal-btn modal-btn-soft">Close</a>
                    </div>
                </div>
            </div>';
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="icon" type="image/png" href="https://i.ibb.co/qYYZs46L/logo.png">

    <title>All Lawyers | SafeSpace PH</title>
    <style>
        .dash-body{
            overflow-y: auto;
        }
        .overlay {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.6);
            transition: opacity 500ms;
            visibility: hidden;
            opacity: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .overlay.active {
            visibility: visible;
            opacity: 1;
        }
        .modal-content {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(57, 16, 83, 0.15);
            padding: 25px 40px;
            max-width: 650px;
            width: 95%;
            position: relative;
            animation: fadeIn 0.3s;
            max-height: 90vh;
            overflow-y: auto; 
        }
        .modal-header {
            text-align: center;
            color: #391053;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            margin-top: 0;
            letter-spacing: 0.5px;
        }
        .modal-divider {
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #391053 0%, #5A2675 30%, #9D72B3 65%, #C9A8F1 100%);
            border: none;
            border-radius: 2px;
            margin: 15px 0 20px 0;
        }
        .modal-body {
            text-align: left;
        }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
        }
        .modal-btn {
            border: none;
            border-radius: 7px;
            padding: 12px 28px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .modal-btn-primary {
            background: #5A2675;
            color: #fff;
        }
        .modal-btn-primary:hover {
            background: #391053;
            box-shadow: 0 4px 15px rgba(90, 38, 117, 0.3);
        }
        .modal-btn-soft {
            background: #f0e9f7;
            color: #5A2675;
        }
        .modal-btn-soft:hover {
            background: #e2d8fa;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px 30px;
            text-align: left;
        }
        .detail-item {
            font-size: 15px;
        }
        .detail-item strong {
            color: #391053;
            display: block;
            margin-bottom: 4px;
            font-weight: 600;
        }
        .detail-item span {
            color: #555;
            word-wrap: break-word;
        }
        .detail-full {
            grid-column: 1 / -1;
        }
        .close-btn { 
             position: absolute; 
             top: 18px; 
             right: 18px; 
             font-size: 24px; 
             font-weight: bold; 
             color: #aaa; 
             cursor: pointer; 
             border: none; 
             background: transparent; 
             line-height: 1; 
             padding: 0; 
             width: 32px; 
             height: 32px; 
             border-radius: 50%; 
             display: flex; 
             align-items: center; 
             justify-content: center; 
             transition: background-color 0.2s, color 0.2s; 
             text-decoration: none;
         } 
         .close-btn:hover { 
             background-color: #f0e9f7; 
             color: #5A2675; 
         }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="menu">
            <table class="menu-container" border="0">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px" >
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?php echo substr($username,0,13)  ?>..</p>
                                    <p class="profile-subtitle"><?php echo substr($useremail,0,22)  ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <a href="../logout.php" ><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                    </table>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-home" >
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Home</p></a></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-report">
                        <a href="report.php" class="non-style-link-menu"><div><p class="menu-text">Report Violation</p></a></div>
                    </td>
                </tr>
                 <tr class="menu-row">
                    <td class="menu-btn menu-icon-schedule">
                        <a href="request-session.php" class="non-style-link-menu"><div><p class="menu-text">Find a Safe Space</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-session">
                        <a href="client-appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Appointments</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-lawyers menu-active menu-icon-lawyers-active">
                        <a href="lawyers.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text menu-text-active">All Lawyers</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></a></div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td>
                        <form action="" method="post" class="header-search">
                            <input type="search" name="search" class="input-text header-searchbar" placeholder="Search Lawyer name or Email" list="lawyers">&nbsp;&nbsp;
                            <?php
                                echo '<datalist id="lawyers">';
                                $list11 = $database->query("select  lawyername,lawyeremail from  lawyer;");
                                for ($y=0;$y<$list11->num_rows;$y++){
                                    $row00=$list11->fetch_assoc();
                                    $d=$row00["lawyername"];
                                    $c=$row00["lawyeremail"];
                                    echo "<option value='".htmlspecialchars($d)."'><br/>";
                                    echo "<option value='".htmlspecialchars($c)."'><br/>";
                                };
                            echo ' </datalist>';
                            ?>
                            <input type="Submit" value="Search" class="login-btn btn-primary btn" style="padding-left: 25px;padding-right: 25px;padding-top: 10px;padding-bottom: 10px;">
                        </form>
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php echo $today; ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top:10px;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">All Lawyers (<?php echo $list11->num_rows; ?>)</p>
                    </td>
                </tr>
                <?php
                    if($_POST){
                        $keyword=$_POST["search"];
                        $sqlmain= "select * from lawyer where lawyeremail='$keyword' or lawyername='$keyword' or lawyername like '$keyword%' or lawyername like '%$keyword' or lawyername like '%$keyword%'";
                    }else{
                        $sqlmain= "select * from lawyer order by lawyerid desc";
                    }
                ?>
                <tr>
                   <td colspan="4">
                       <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0">
                        <thead>
                        <tr>
                                <th class="table-headin">Lawyer Name</th>
                                <th class="table-headin">Email</th>
                                <th class="table-headin">Specialties</th>
                                <th class="table-headin">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php
                                $result= $database->query($sqlmain);
                                if($result->num_rows==0){
                                    echo '<tr>
                                    <td colspan="4">
                                    <br><br><br><br>
                                    <center>
                                    <img src="../img/notfound.svg" width="25%">
                                    <br>
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We couldn\'t find anything related to your keywords!</p>
                                    <a class="non-style-link" href="lawyers.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Lawyers &nbsp;</button>
                                    </a>
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                    </tr>';
                                }
                                else{
                                for ( $x=0; $x<$result->num_rows;$x++){
                                    $row=$result->fetch_assoc();
                                    $lawyerid=$row["lawyerid"];
                                    $name=$row["lawyername"];
                                    $email=$row["lawyeremail"];
                                    $spe=$row["specialties"];
                                    $spcil_res= $database->query("select sname from specialties where id='$spe'");
                                    $spcil_array= $spcil_res->fetch_assoc();
                                    $spcil_name = $spcil_array ? $spcil_array["sname"] : "N/A";
                                    echo '<tr>
                                        <td> &nbsp;'. htmlspecialchars(substr($name,0,30)) .'</td>
                                        <td>'. htmlspecialchars(substr($email,0,20)) .'</td>
                                        <td>'. htmlspecialchars(substr($spcil_name,0,20)) .'</td>
                                        <td>
                                        <div style="display:flex;justify-content: center;">
                                        <a href="?action=view&id='.$lawyerid.'" class="non-style-link"><button  class="btn-primary-soft btn button-icon btn-view"  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">View</font></button></a>
                                       &nbsp;&nbsp;&nbsp;
                                       <a href="?action=session&id='.$lawyerid.'&name='.urlencode($name).'"  class="non-style-link"><button  class="btn-primary-soft btn button-icon btn-session"  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Sessions</font></button></a>
                                        </div>
                                        </td>
                                    </tr>';
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                        </div>
                        </center>
                   </td> 
                </tr>
            </table>
        </div>
    </div>
    <?php 
        // Echo the modal HTML here, at the end of the body
        echo $modal_html;
    ?>
</body>
</html>
