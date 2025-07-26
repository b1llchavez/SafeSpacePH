 <?php



    
    


    include("../connection.php");

    
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'unverify_lawyer') {
        $lawyer_id = $_POST['lawyer_id'];
        $lawyer_email = $_POST['lawyer_email'];
    
        $database->begin_transaction();
    
        try {
            $stmt_check_usertype = $database->prepare("SELECT usertype FROM webuser WHERE email = ?");
            if ($stmt_check_usertype) {
                $stmt_check_usertype->bind_param("s", $lawyer_email);
                $stmt_check_usertype->execute();
                $result_check_usertype = $stmt_check_usertype->get_result();
                $user_data = $result_check_usertype->fetch_assoc();
                $stmt_check_usertype->close();
    
                if ($user_data && $user_data['usertype'] == 'u') {
                    throw new Exception("unverified_already");
                }
            } else {
                throw new Exception("Failed to prepare usertype check statement: " . $database->error);
            }
    
            $stmt_webuser_update = $database->prepare("UPDATE webuser SET usertype = 'u' WHERE email = ?");
            if ($stmt_webuser_update) {
                $stmt_webuser_update->bind_param("s", $lawyer_email);
                $stmt_webuser_update->execute();
                $stmt_webuser_update->close();
            } else {
                throw new Exception("Failed to prepare webuser update statement: " . $database->error);
            }
    
            $database->commit();
    
            header("Location: lawyers.php?message=unverified_success");
            exit();
    
        } catch (Exception $e) {
            $database->rollback();
            error_log("Lawyer unverification failed: " . $e->getMessage());
            if ($e->getMessage() == "unverified_already") {
                header("Location: lawyers.php?message=unverified_already");
            } else {
                header("Location: lawyers.php?message=unverified_error&details=" . urlencode($e->getMessage()));
            }
            exit();
        }
    }
    
    if (isset($_GET['message'])) {
        $action_result = $_GET['message'];
        $popup_title = '';
        $popup_content = '';
        $is_error = false;
        $header_color = '#5A2675';
    
        switch ($action_result) {
            case 'unverified_success':
                $popup_title = "Verification Revoked!";
                $popup_content = "Lawyer has been successfully unverified.";
                break;
            case 'unverified_error':
                $is_error = true;
                $popup_title = "Unverification Error!";
                $popup_content = "Lawyer unverification failed. Please try again.";
                if (isset($_GET['details'])) {
                    $popup_content .= '<br><small style="color:#555;">Details: ' . htmlspecialchars($_GET['details']) . '</small>';
                }
                break;
            case 'unverified_already':
                $is_error = true;
                $popup_title = "Warning";
                $popup_content = 'This user is already unverified or was never verified.';
                break;
        }
        
        if ($is_error) {
            $header_color = '#dc3545';
        }
    
        if (!empty($popup_title)) {
            echo '
            <div id="messagePopup" class="overlay" style="display: flex;">
                <div class="modal-content" style="max-width: 450px;">
                    <h2 class="modal-header" style="color: '. $header_color .';">
                        '. $popup_title .'
                    </h2>
                    <div class="modal-divider"></div>
                    <div class="modal-body">
                        <p>'. $popup_content .'</p>
                    </div>
                    <div class="modal-footer">
                        <a href="lawyers.php" class="non-style-link">
                            <button type="button" class="modal-btn modal-btn-primary">OK</button>
                        </a>
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
    <link rel="icon" type="image/png" href="../img/logo.png">   

    <title>All Lawyers | SafeSpace PH</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
         
        .dash-body{
            overflow-y: auto;
        }
        .overlay {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            transition: opacity 500ms;
            display: none;  
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .modal-content {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(57, 16, 83, 0.15);
            padding: 30px 40px;
            max-width: 600px;
            width: 90%;
            position: relative;
            animation: fadeIn 0.4s ease-out;
            margin: 0 auto;
        }

        .modal-header {
            text-align: center;
            color: #391053;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            margin-top: 0;
            letter-spacing: 0.5px;
            position: relative;
        }

        .modal-divider {
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #391053 0%, #5A2675 30%, #9D72B3 65%, #C9A8F1 100%);
            border: none;
            border-radius: 2px;
            margin: 18px 0 28px 0;
        }

        .modal-body {
            text-align: center;
            font-size: 16px;
            color: #444;
            line-height: 1.6;
        }

        .modal-footer {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
        }
        
        .modal-btn {
            border: none;
            border-radius: 7px;
            padding: 12px 28px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        .modal-btn-primary {
            background-color: #5A2675;
            color: white;
        }
        .modal-btn-primary:hover {
            background-color: #5A2675;
        }
        .modal-btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .modal-btn-danger:hover {
            background-color: #c82333;
        }
        .modal-btn-secondary {
            background: #f0f0f0;
            color: #555;
            border: 1px solid #ddd;
        }
        .modal-btn-secondary:hover {
            background: #e0e0e0;
            border-color: #ccc;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* --- Custom Styles for Sidebar Adjustment (Final Fix) --- */
        /* 1. Reduce the overall width of the sidebar menu */
        .menu {
            width: 250px; 
        }
        /* 2. Adjust all menu items for new width and spacing */
        .menu-btn {
            /* Position icon closer to the left edge */
            background-position: 52px center !important;
            /* Compress vertical padding and adjust left padding for icon */
            padding: 9px 15px 9px 4px !important;
        }
        /* 3. Force menu text to a single line */
        .menu-text {
            font-size: 14px;
            white-space: nowrap; /* Prevents text from wrapping */
            overflow: hidden; /* Hides any part of the text that still overflows */
            text-overflow: ellipsis; /* Adds "..." if text is too long for the container */
        }
        /* 4. Compact the Profile Container */
        .profile-container td {
            padding: 0 5px; /* Reduce padding on cells */
        }
        .profile-container .profile-info-cell {
            padding-left: 10px !important;
        }
        .profile-title {
            font-size: 15px;
            margin-bottom: 2px;
        }
        .profile-subtitle {
            font-size: 12px;
            word-break: break-all;
        }
        .logout-btn {
            width: 100%;
            padding: 8px !important;
            margin-top: 8px !important;
            font-size: 13px;
        }
        /* --- End of Custom Styles --- */
</style>
</head>
<body>
   
    <div class="container">
        <div class="menu">
            <table class="menu-container" border="0">
                <tr>
                    <td style="padding:15px 10px;" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="25%" style="padding-left:10px">
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td class="profile-info-cell" style="vertical-align: middle;">
                                    <p class="profile-title">Administrator</p>
                                    <p class="profile-subtitle">admin@safespaceph.com</p>
                                    <a href="../logout.php">
                                        <input type="button" value="Log out" class="logout-btn btn-primary-soft btn">
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-dashbord">
                        <a href="index.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Dashboard</p>
                            </div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-report">
                        <a href="admin_reports.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Violation Reports</p>
                            </div>
                        </a>
                    </td>
                </tr>
                     </tr>
                     <tr class="menu-row">
                    <td class="menu-btn menu-icon-schedule">
                        <a href="schedule.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Session Requests</p>
                            </div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Appointments</p>
                            </div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-client-verification">
                        <a href="client_verification.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Client Verification</p>
                            </div>
                        </a>
                    </td>
                </tr>
               <tr class="menu-row">
                    <td class="menu-btn menu-icon-lawyer-verification">
                        <a href="lawyer_verification.php" class="non-style-link-menu"><div><p class="menu-text">Lawyer Verification</p></div></a>
                    </td>
                </tr> 
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-lawyers menu-active menu-icon-lawyers-active">
                        <a href="lawyers.php" class="non-style-link-menu non-style-link-menu-active">
                            <div>
                                <p class="menu-text menu-text-active">All Lawyers</p>
                            </div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-client">
                        <a href="client.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">All Clients</p>
                            </div>
                        </a>
                    </td>
                </tr>
            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td colspan="3">                
                        <form action="" method="post" class="header-search">

                            <input type="search" name="search" class="input-text header-searchbar" placeholder="Search Lawyer name or Email" list="lawyers">&nbsp;&nbsp;
                            
                            <?php
                                echo '<datalist id="lawyers">';
                                $list11 = $database->query("select  lawyername,lawyeremail from  lawyer;");

                                for ($y=0;$y<$list11->num_rows;$y++){
                                    $row00=$list11->fetch_assoc();
                                    $l=$row00["lawyername"];
                                    $c=$row00["lawyeremail"];
                                    echo "<option value='$l'><br/>";
                                    echo "<option value='$c'><br/>";
                                };

                            echo ' </datalist>';
?>
                            <input type="hidden" name="lawyerid" value="<?php echo isset($lawyerid) ? $lawyerid : ''; ?>">
                            <input type="Submit" value="Search" class="login-btn btn-primary btn" style="padding-left: 25px;padding-right: 25px;padding-top: 10px;padding-bottom: 10px;">
                        
                        </form>
                        
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php 
                        date_default_timezone_set('Asia/Kolkata');

                        $date = date('Y-m-d');
                        echo $date;
                        ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>

                </tr>
               
                <tr>
    <td colspan="3" style="padding-top:30px;">
        <p class="heading-main12" style="margin-left: 45px; font-size:20px; color:rgb(49, 49, 49); margin-bottom: 0;">
            Lawyers Manager
        </p>
    </td>
    <td colspan="2" style="text-align: right; padding-top:30px; padding-right: 45px; white-space: nowrap;">
        <a href="?action=add&id=none&error=0" class="non-style-link">
            <button class="login-btn btn-primary btn button-icon"
                style="display: inline-flex; align-items: center; background-image: url('../img/icons/add.svg'); white-space: nowrap; min-width: 170px;">
                Add New Lawyer
            </button>
        </a>
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
                   <td colspan="5">
                       <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0">
                        <thead>
                        <tr>
                                <th class="table-headin">
                                    
                                
                                Lawyer Name
                                
                                </th>
                                <th class="table-headin">
                                    Email
                                </th>
                                <th class="table-headin">
                                    
                                    Specialties
                                    
                                </th>
                                <th class="table-headin">
                                    
                                    Action
                                    
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
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We  couldnt find anything related to your keywords !</p>
                                    <a class="non-style-link" href="lawyers.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Lawyers &nbsp;</font></button>
                                    </a>
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                    </tr>';
                                    
                                }
                                else{
                                for ($x = 0; $x < $result->num_rows; $x++) {
                                    $row = $result->fetch_assoc();
                                    $lawyerid = $row["lawyerid"];
                                    $name = $row["lawyername"];
                                    $email = $row["lawyeremail"];
                                    $spe = $row["specialties"];
                                    $spcil_res = $database->query("select sname from specialties where id='$spe'");
                                    $spcil_array = $spcil_res ? $spcil_res->fetch_assoc() : null;
                                    $spcil_name = ($spcil_array && isset($spcil_array["sname"])) ? $spcil_array["sname"] : "N/A";
                                    echo '<tr>
                                        <td> &nbsp;' .
                                        substr($name, 0, 30)
                                        . '</td>
                                        <td>
                                        ' . substr($email, 0, 20) . '
                                        </td>
                                        <td>
                                            ' . substr($spcil_name, 0, 20) . '
                                        </td>

                                        <td>
                                        <div style="display:flex;justify-content: center;">
                                        <a href="?action=edit&id=' . $lawyerid . '&error=0" class="non-style-link"><button  class="btn-primary-soft btn button-icon btn-edit"  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Edit</font></button></a>
                                        &nbsp;&nbsp;&nbsp;
                                        <a href="?action=view&id=' . $lawyerid . '" class="non-style-link"><button  class="btn-primary-soft btn button-icon btn-view"  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">View</font></button></a>
                                       &nbsp;&nbsp;&nbsp;
                                        <button class="btn-primary-soft btn button-icon btn-unverify"
                                            onclick="event.stopPropagation(); showUnverifyConfirmModal(\''.$lawyerid.'\', \''.$email.'\')"
                                            style="padding-left: 40px; padding-top: 12px; padding-bottom: 12px; margin-top: 10px;">
                                            <font class="tn-in-text">Unverify</font>
                                        </button>
                                       &nbsp;&nbsp;&nbsp;
                                       <a href="?action=drop&id=' . $lawyerid . '&name=' . $name . '" class="non-style-link"><button  class="btn-primary-soft btn button-icon btn-delete"  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Remove</font></button></a>
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
    <div id="unverifyConfirmModal" class="overlay">
        <div class="modal-content" style="max-width: 500px;">
            <h2 class="modal-header">Confirm Unverification</h2>
            <div class="modal-divider"></div>
            <div class="modal-body">
                <p>Are you sure you want to unverify this lawyer?</p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-primary" id="confirmUnverifyBtn">Yes, Unverify</button>
                <button type="button" class="modal-btn modal-btn-secondary" onclick="hideUnverifyConfirmModal()">Cancel</button>
            </div>
        </div>
    </div>
    <?php 
    if(isset($_GET['action'])){
        
        $id=$_GET["id"];
        $action=$_GET["action"];
        if($action=='drop'){
            $nameget=$_GET["name"];
            echo '
            <div id="popup1" class="overlay" style="display:flex;">
                <div class="modal-content" style="max-width: 500px;">
                    <h2 class="modal-header" style="color:#dc3545;">Are you sure?</h2>
                    <div class="modal-divider"></div>
                    <div class="modal-body">
                        <p>You want to delete this record<br>('.substr($nameget,0,40).').</p>
                        <p style="color: #dc3545; font-weight: bold; margin-top: 10px;">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <a href="delete-lawyer.php?id='.$id.'" class="modal-btn modal-btn-danger">Yes, Delete</a>
                        <a href="lawyers.php" class="modal-btn modal-btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
            ';
        }elseif($action=='view'){
            $sqlmain= "select * from lawyer where lawyerid='$id'";
            $result= $database->query($sqlmain);
            $row=$result->fetch_assoc();
            $name=$row["lawyername"];
            $email=$row["lawyeremail"];
            $spe=$row["specialties"];
            
            $spcil_res= $database->query("select sname from specialties where id='$spe'");
            $spcil_array= $spcil_res->fetch_assoc();
            $spcil_name=$spcil_array["sname"];
            $tele=$row['lawyertel'];
            echo '
<div id="viewDetailsModal" style="display:flex; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
    <div style="background-color:#fff; padding:30px; border-radius:8px; width:90%; max-width:700px; box-shadow:0 4px 12px rgba(0,0,0,0.3); position:relative;">
        <h3 style="text-align:center; color:#391053; font-size:1.8rem; font-weight:700; margin:0 0 10px 0; letter-spacing:0.5px;">View Details</h3>
        <div style="width:100%; height:3px; background:linear-gradient(90deg, #391053 0%, #5A2675 30%, #9D72B3 65%, #C9A8F1 100%); border-radius:2px; margin:18px 0 28px 0;"></div>

        <div style="margin-bottom:25px;">
            <div style="margin-bottom:10px;"><strong>Name:</strong> <span>' . htmlspecialchars($name) . '</span></div>
            <div style="margin-bottom:10px;"><strong>Email:</strong> <span>' . htmlspecialchars($email) . '</span></div>
            <div style="margin-bottom:10px;"><strong>Valid ID:</strong> <span></span></div>
            <div style="margin-bottom:10px;"><strong>Telephone:</strong> <span>' . htmlspecialchars($tele) . '</span></div>
            <div style="margin-bottom:10px;"><strong>Specialties:</strong> <span>' . htmlspecialchars($spcil_name) . '</span></div>
        </div>

        <div style="display:flex; justify-content:center; margin-top:30px;">
            <a href="lawyers.php" style="text-decoration:none;">
                <button style="
                    border: none;
                    border-radius: 7px;
                    padding: 12px 28px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    background: #f0e9f7;
                    color: #5A2675;
                    transition: background 0.2s, box-shadow 0.2s;
                ">Close</button>
            </a>
        </div>
    </div>
</div>';
        }elseif($action=='add'){
                $error_1=$_GET["error"];
                $errorlist= array(
                    '1'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Already have an account for this Email address.</label>',
                    '2'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password Conformation Error! Reconform Password</label>',
                    '3'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;"></label>',
                    '4'=>"",
                    '0'=>'',

                );
                if($error_1!='4'){
                echo '
<div id="popup1" style="display:flex; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
    <div style="background-color:#fff; padding:30px 35px; border-radius:10px; width:90%; max-width:720px; box-shadow:0 4px 12px rgba(0,0,0,0.3); position:relative; font-family:Arial, sans-serif;">
        <a href="lawyers.php" style="position:absolute; top:15px; right:20px; font-size:28px; font-weight:bold; text-decoration:none; color:#391053;">&times;</a>

        <h3 style="text-align:center; color:#391053; font-size:1.8rem; font-weight:700; margin-bottom:10px;">Add New Lawyer</h3>
        <div style="width:100%; height:3px; background:linear-gradient(90deg, #391053 0%, #5A2675 30%, #9D72B3 65%, #C9A8F1 100%); border-radius:2px; margin:18px 0 30px 0;"></div>

        <div style="color:#D8000C; font-weight:500; text-align:center; margin-bottom:20px;">'.
            $errorlist[$error_1]
        .'</div>

        <form action="add-new.php" method="POST" style="display:flex; flex-direction:column; gap:18px;">
            <div>
                <label style="font-weight:600; color:#391053;">Name:</label><br>
                <input type="text" name="name" placeholder="Lawyer Name" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
            </div>

            <div>
                <label style="font-weight:600; color:#391053;">Email:</label><br>
                <input type="email" name="email" placeholder="Email Address" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
            </div>

            <div>
                <label style="font-weight:600; color:#391053;">Valid ID:</label><br>
                <input type="text" name="lawyerrollid" placeholder="Valid ID Number" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
            </div>

            <div>
                <label style="font-weight:600; color:#391053;">Telephone:</label><br>
                <input type="tel" name="Tele" placeholder="Telephone Number" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
            </div>

            <div>
                <label style="font-weight:600; color:#391053;">Choose Specialties:</label><br>
                <select name="spec" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">';

                    $list11 = $database->query("SELECT * FROM specialties ORDER BY sname ASC;");
                    while ($row00 = $list11->fetch_assoc()) {
                        $sn = $row00["sname"];
                        $id00 = $row00["id"];
                        echo "<option value=\"$id00\">$sn</option>";
                    }

echo '          </select>
            </div>

            <div>
                <label style="font-weight:600; color:#391053;">Password:</label><br>
                <input type="password" name="password" placeholder="Define a Password" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
            </div>

            <div>
                <label style="font-weight:600; color:#391053;">Confirm Password:</label><br>
                <input type="password" name="cpassword" placeholder="Confirm Password" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
            </div>

            <div style="display:flex; justify-content:center; gap:20px; margin-top:10px;">
                <input type="reset" value="Reset" style="border:none; padding:10px 25px; background:#f0e9f7; color:#5A2675; border-radius:7px; font-weight:600; cursor:pointer;">
                <input type="submit" value="Add" style="border:none; padding:10px 25px; background:#5A2675; color:#fff; border-radius:7px; font-weight:600; cursor:pointer;">
            </div>
        </form>
    </div>
</div>';


            }else{
                echo '
                    <div id="popup1" class="overlay">
                            <div class="popup">
                            <center>
                            <br><br><br><br>
                                <h2>New Record Added Successfully!</h2>
                                <a class="close" href="lawyers.php">&times;</a>
                                <div class="content">
                                    
                                    
                                </div>
                                <div style="display: flex;justify-content: center;">

                                <a href="lawyers.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>

                                </div>
                                <br><br>
                            </center>
                    </div>
                    </div>
        ';
            }
        }elseif($action=='edit'){
            $sqlmain= "select * from lawyer where lawyerid='$id'";
            $result= $database->query($sqlmain);
            $row=$result->fetch_assoc();
            $name=$row["lawyername"];
            $email=$row["lawyeremail"];
            $spe=$row["specialties"];
            
            $spcil_res= $database->query("select sname from specialties where id='$spe'");
            $spcil_array= $spcil_res->fetch_assoc();
            $spcil_name=$spcil_array["sname"];
            $lawyerrollid =$row['lawyerrollid'];
            $tele=$row['lawyertel'];

            $error_1=$_GET["error"];
                $errorlist= array(
                    '1'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Already have an account for this Email address.</label>',
                    '2'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password Conformation Error! Reconform Password</label>',
                    '3'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;"></label>',
                    '4'=>"",
                    '0'=>'',

                );

            if($error_1!='4'){
                   echo '
<div id="popup1" style="display:flex; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
    <div style="background-color:#fff; padding:30px 35px; border-radius:10px; width:90%; max-width:720px; box-shadow:0 4px 12px rgba(0,0,0,0.3); position:relative; font-family:Arial, sans-serif;">
        <a href="lawyers.php" style="position:absolute; top:15px; right:20px; font-size:28px; font-weight:bold; text-decoration:none; color:#391053;">&times;</a>

        <h3 style="text-align:center; color:#391053; font-size:1.8rem; font-weight:700; margin-bottom:10px;">Edit Lawyer Details</h3>
        <div style="width:100%; height:3px; background:linear-gradient(90deg, #391053 0%, #5A2675 30%, #9D72B3 65%, #C9A8F1 100%); border-radius:2px; margin:18px 0 30px 0;"></div>

        <div style="color:#D8000C; font-weight:500; text-align:center; margin-bottom:20px;">' . $errorlist[$error_1] . '</div>

        <form action="edit-lawyer.php" method="POST" style="display:flex; flex-direction:column; gap:18px;">
            <input type="hidden" name="id00" value="'.$id.'">
            <input type="hidden" name="oldemail" value="'.$email.'">

            <div>
                <label style="font-weight:600; color:#391053;">Email:</label><br>
                <input type="email" name="email" value="'.$email.'" placeholder="Email Address" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
            </div>

            <div>
                <label style="font-weight:600; color:#391053;">Name:</label><br>
                <input type="text" name="name" value="'.$name.'" placeholder="Lawyer Name" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
            </div>

            <div>
                <label style="font-weight:600; color:#391053;">Valid ID:</label><br>
                <input type="text" name="lawyerrollid" value="'.$lawyerrollid.'" placeholder="Valid ID Number" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
            </div>

            <div>
                <label style="font-weight:600; color:#391053;">Telephone:</label><br>
                <input type="tel" name="Tele" value="'.$tele.'" placeholder="Telephone Number" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
            </div>

            <div>
                <label style="font-weight:600; color:#391053;">Choose Specialties: (Current: '.$spcil_name.')</label><br>
                <select name="spec" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">';

$list11 = $database->query("SELECT * FROM specialties ORDER BY sname ASC;");
while ($row00 = $list11->fetch_assoc()) {
    $sn = $row00["sname"];
    $id00 = $row00["id"];
    echo "<option value=\"$id00\">$sn</option>";
}

echo '
                </select>
            </div>

            <div>
                <label style="font-weight:600; color:#391053;">Password:</label><br>
                <input type="password" name="password" placeholder="Define a Password" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
            </div>

            <div>
                <label style="font-weight:600; color:#391053;">Confirm Password:</label><br>
                <input type="password" name="cpassword" placeholder="Confirm Password" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
            </div>

            <div style="display:flex; justify-content:center; gap:20px; margin-top:10px;">
                <input type="reset" value="Reset" style="border:none; padding:10px 25px; background:#f0e9f7; color:#5A2675; border-radius:7px; font-weight:600; cursor:pointer;">
                <input type="submit" value="Save" style="border:none; padding:10px 25px; background:#5A2675; color:#fff; border-radius:7px; font-weight:600; cursor:pointer;">
            </div>
        </form>
    </div>
</div>';

        }else{
            echo '
                <div id="popup1" class="overlay">
                        <div class="popup">
                        <center>
                        <br><br><br><br>
                            <h2>Edit Successfully!</h2>
                            <a class="close" href="lawyers.php">&times;</a>
                            <div class="content">
                                
                                
                            </div>
                            <div style="display: flex;justify-content: center;">

                            <a href="lawyers.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>

                            </div>
                            <br><br>
                        </center>
                </div>
                </div>
    ';



        }; };
    };

?>
</div>
<script>
    let currentLawyerId = null;
    let currentLawyerEmail = null;

    function showUnverifyConfirmModal(lawyerId, lawyerEmail) {
        currentLawyerId = lawyerId;
        currentLawyerEmail = lawyerEmail;
        document.getElementById('unverifyConfirmModal').style.display = 'flex';
    }

    function hideUnverifyConfirmModal() {
        document.getElementById('unverifyConfirmModal').style.display = 'none';
        currentLawyerId = null;
        currentLawyerEmail = null;
    }
    document.getElementById('confirmUnverifyBtn').addEventListener('click', function() {
        if (currentLawyerId && currentLawyerEmail) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'lawyers.php';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'unverify_lawyer';
            form.appendChild(actionInput);

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'lawyer_id';
            idInput.value = currentLawyerId;
            form.appendChild(idInput);

            const emailInput = document.createElement('input');
            emailInput.type = 'hidden';
            emailInput.name = 'lawyer_email';
            emailInput.value = currentLawyerEmail;
            form.appendChild(emailInput);

            document.body.appendChild(form);
            form.submit();
        }
        hideUnverifyConfirmModal();
    });
</script>

</body>
</html>