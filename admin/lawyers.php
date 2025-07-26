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

        /* --- Custom Styles from admin_reports.php --- */
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            color: #fff;
            font-size: 12px;
            text-align: center;
        }
        .status-verified { background-color: #28a745; }
        .status-unverified { background-color: #ffc107; }
        .status-unknown { background-color: #6c757d; }

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
        .overlay.view-modal-overlay {
            display: block;
            overflow-y: auto;
            padding: 30px 15px;
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
        
        .modal-body-left {
            text-align: left;
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
            background-color: #4a1e63;
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
        .modal-btn-soft {
            background: #f0e9f7;
            color: #5A2675;
        }
        .modal-btn-soft:hover {
            background: #e2d8fa;
        }
        .form-label {
            font-weight: 600;
            color: #391053;
            margin-bottom: 8px;
            display: block;
        }
        .form-input {
            width: 100%;
            padding: 12px;
            border-radius: 7px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-input:focus {
            border-color: #5A2675;
            box-shadow: 0 0 0 3px rgba(90, 38, 117, 0.2);
            outline: none;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* --- Custom Styles for Sidebar Adjustment (Final Fix) --- */
        .menu {
            width: 250px; 
        }
        .menu-btn {
            background-position: 52px center !important;
            padding: 9px 15px 9px 4px !important;
        }
        .menu-text {
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .profile-container td {
            padding: 0 5px;
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
                                <th class="table-headin">Lawyer Name</th>
                                <th class="table-headin">Email</th>
                                <th class="table-headin">Specialties</th>
                                <th class="table-headin">Status</th>
                                <th class="table-headin">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        
                            <?php

                                
                                $result= $database->query($sqlmain);

                                if($result->num_rows==0){
                                    echo '<tr>
                                    <td colspan="5">
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

                                    $status = "Unknown";
                                    $status_class = "status-unknown";
                                
                                    $stmt_status = $database->prepare("SELECT usertype FROM webuser WHERE email = ?");
                                    if ($stmt_status) {
                                        $stmt_status->bind_param("s", $email);
                                        $stmt_status->execute();
                                        $status_result = $stmt_status->get_result();
                                        if ($status_data = $status_result->fetch_assoc()) {
                                            $usertype = $status_data['usertype'];
                                            if ($usertype == 'l' || $usertype == 'a') {
                                                $status = "Verified";
                                                $status_class = "status-verified";
                                            } elseif ($usertype == 'u') {
                                                $status = "Unverified";
                                                $status_class = "status-unverified";
                                            }
                                        }
                                        $stmt_status->close();
                                    }

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
                                        <td><span class="status-badge ' . $status_class . '">' . $status . '</span></td>
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
                                       <a href="?action=drop&id=' . $lawyerid . '&name=' . urlencode($name) . '" class="non-style-link"><button  class="btn-primary-soft btn button-icon btn-delete"  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Remove</font></button></a>
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
            <h2 class="modal-header" style="color:#dc3545;">Confirm Unverification</h2>
            <div class="modal-divider"></div>
            <div class="modal-body">
                <p>Are you sure you want to unverify this lawyer?</p>
                <p>This will revoke their verified status and change their user type.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="hideUnverifyConfirmModal()">Cancel</button>
                <form id="unverifyForm" method="POST" action="lawyers.php" style="margin:0;">
                     <input type="hidden" name="action" value="unverify_lawyer">
                     <input type="hidden" name="lawyer_id" id="unverifyLawyerId">
                     <input type="hidden" name="lawyer_email" id="unverifyLawyerEmail">
                     <button type="submit" class="modal-btn modal-btn-danger">Yes, Unverify</button>
                </form>
            </div>
        </div>
    </div>
    <?php 
    if(isset($_GET['action'])){
        
        $id=$_GET["id"];
        $action=$_GET["action"];
        if($action=='drop'){
            $nameget=urldecode($_GET["name"]);
            echo '
            <div id="popup1" class="overlay" style="display:flex;">
                <div class="modal-content" style="max-width: 500px;">
                    <h2 class="modal-header" style="color:#dc3545;">Are you sure?</h2>
                    <div class="modal-divider"></div>
                    <div class="modal-body">
                        <p>You are about to permanently delete the record for<br><strong>'.htmlspecialchars($nameget).'</strong>.</p>
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
            $lawyerrollid =$row['lawyerrollid'];
            
            $spcil_res= $database->query("select sname from specialties where id='$spe'");
            $spcil_array= $spcil_res->fetch_assoc();
            $spcil_name= $spcil_array ? $spcil_array["sname"] : "N/A";
            $tele=$row['lawyertel'];
            echo '
            <div id="viewDetailsModal" class="overlay" style="display:flex;">
                <div class="modal-content" style="max-width: 700px;">
                    <h2 class="modal-header">View Lawyer Details</h2>
                    <div class="modal-divider"></div>
                    <div class="modal-body modal-body-left">
                        <div class="detail-item" style="display:flex; margin-bottom: 15px; font-size: 16px;">
                            <strong style="width: 150px; color: #555;">Name:</strong>
                            <span>' . htmlspecialchars($name) . '</span>
                        </div>
                        <div class="detail-item" style="display:flex; margin-bottom: 15px; font-size: 16px;">
                            <strong style="width: 150px; color: #555;">Email:</strong>
                            <span>' . htmlspecialchars($email) . '</span>
                        </div>
                        <div class="detail-item" style="display:flex; margin-bottom: 15px; font-size: 16px;">
                            <strong style="width: 150px; color: #555;">Valid ID:</strong>
                            <span>' . htmlspecialchars($lawyerrollid) . '</span>
                        </div>
                        <div class="detail-item" style="display:flex; margin-bottom: 15px; font-size: 16px;">
                            <strong style="width: 150px; color: #555;">Telephone:</strong>
                            <span>' . htmlspecialchars($tele) . '</span>
                        </div>
                        <div class="detail-item" style="display:flex; margin-bottom: 15px; font-size: 16px;">
                            <strong style="width: 150px; color: #555;">Specialties:</strong>
                            <span>' . htmlspecialchars($spcil_name) . '</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="lawyers.php" class="non-style-link">
                            <button type="button" class="modal-btn modal-btn-soft">Close</button>
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
                <div id="popup1" class="overlay" style="display:flex;">
                    <div class="modal-content" style="max-width: 720px;">
                        <a href="lawyers.php" class="close" style="position:absolute; top:10px; right:15px; font-size:2rem; text-decoration:none; color:#888;">&times;</a>
                        <h2 class="modal-header">Add New Lawyer</h2>
                        <div class="modal-divider"></div>
                        <div class="modal-body modal-body-left">
                            <form action="add-new.php" method="POST" style="display:flex; flex-direction:column; gap:18px;">
                                <div style="text-align:center; margin-bottom: 10px;">'.$errorlist[$error_1].'</div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                    <div>
                                        <label class="form-label">Full Name:</label>
                                        <input type="text" name="name" class="form-input" placeholder="Lawyer Name" required>
                                    </div>
                                    <div>
                                        <label class="form-label">Email:</label>
                                        <input type="email" name="email" class="form-input" placeholder="Email Address" required>
                                    </div>
                                    <div>
                                        <label class="form-label">Valid ID:</label>
                                        <input type="text" name="lawyerrollid" class="form-input" placeholder="Valid ID Number" required>
                                    </div>
                                    <div>
                                        <label class="form-label">Telephone:</label>
                                        <input type="tel" name="Tele" class="form-input" placeholder="Telephone Number" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">Choose Specialties:</label>
                                    <select name="spec" required class="form-input">';
                                        $list11 = $database->query("SELECT * FROM specialties ORDER BY sname ASC;");
                                        while ($row00 = $list11->fetch_assoc()) {
                                            $sn = $row00["sname"];
                                            $id00 = $row00["id"];
                                            echo "<option value=\"".htmlspecialchars($id00)."\">".htmlspecialchars($sn)."</option>";
                                        }
                echo '              </select>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                    <div>
                                        <label class="form-label">Password:</label>
                                        <input type="password" name="password" class="form-input" placeholder="Define a Password" required>
                                    </div>
                                    <div>
                                        <label class="form-label">Confirm Password:</label>
                                        <input type="password" name="cpassword" class="form-input" placeholder="Confirm Password" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <input type="reset" value="Reset" class="modal-btn modal-btn-secondary">
                                    <input type="submit" value="Add" class="modal-btn modal-btn-primary">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>';
            }else{
                echo '
                    <div id="popup1" class="overlay" style="display:flex;">
                        <div class="modal-content" style="max-width: 450px;">
                            <h2 class="modal-header" style="color: #28a745;">Success!</h2>
                            <div class="modal-divider"></div>
                            <div class="modal-body">
                                <p>New Lawyer Added Successfully!</p>
                            </div>
                            <div class="modal-footer">
                                <a href="lawyers.php" class="modal-btn modal-btn-primary">OK</a>
                            </div>
                        </div>
                    </div>';
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
                   <div id="popup1" class="overlay" style="display:flex;">
                       <div class="modal-content" style="max-width: 720px;">
                           <a href="lawyers.php" class="close" style="position:absolute; top:10px; right:15px; font-size:2rem; text-decoration:none; color:#888;">&times;</a>
                           <h2 class="modal-header">Edit Lawyer Details</h2>
                           <div class="modal-divider"></div>
                           <div class="modal-body modal-body-left">
                               <form action="edit-lawyer.php" method="POST" style="display:flex; flex-direction:column; gap:18px;">
                                   <input type="hidden" name="id00" value="'.htmlspecialchars($id).'">
                                   <div style="text-align:center; margin-bottom: 10px;">' . $errorlist[$error_1] . '</div>
                                   <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                       <div>
                                           <label class="form-label">Full Name:</label>
                                           <input type="text" name="name" class="form-input" value="'.htmlspecialchars($name).'" required>
                                       </div>
                                       <div>
                                           <label class="form-label">Email:</label>
                                           <input type="email" name="email" class="form-input" value="'.htmlspecialchars($email).'" required>
                                           <input type="hidden" name="oldemail" value="'.htmlspecialchars($email).'">
                                       </div>
                                       <div>
                                           <label class="form-label">Valid ID:</label>
                                           <input type="text" name="lawyerrollid" class="form-input" value="'.htmlspecialchars($lawyerrollid).'" required>
                                       </div>
                                       <div>
                                           <label class="form-label">Telephone:</label>
                                           <input type="tel" name="Tele" class="form-input" value="'.htmlspecialchars($tele).'" required>
                                       </div>
                                   </div>
                                   <div>
                                       <label class="form-label">Choose Specialties: (Current: '.htmlspecialchars($spcil_name).')</label>
                                       <select name="spec" required class="form-input">';
                                           $list11 = $database->query("SELECT * FROM specialties ORDER BY sname ASC;");
                                           while ($row00 = $list11->fetch_assoc()) {
                                               $sn = $row00["sname"];
                                               $id00 = $row00["id"];
                                               $selected = ($id00 == $spe) ? "selected" : "";
                                               echo "<option value=\"".htmlspecialchars($id00)."\" ".$selected.">".htmlspecialchars($sn)."</option>";
                                           }
                   echo '              </select>
                                   </div>
                                   <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                       <div>
                                           <label class="form-label">New Password:</label>
                                           <input type="password" name="password" class="form-input" placeholder="Leave blank to keep current password">
                                       </div>
                                       <div>
                                           <label class="form-label">Confirm New Password:</label>
                                           <input type="password" name="cpassword" class="form-input" placeholder="Confirm new password">
                                       </div>
                                   </div>
                                   <div class="modal-footer">
                                       <input type="reset" value="Reset" class="modal-btn modal-btn-secondary">
                                       <input type="submit" value="Save Changes" class="modal-btn modal-btn-primary">
                                   </div>
                               </form>
                           </div>
                       </div>
                   </div>';
        }else{
            echo '
            <div id="popup1" class="overlay" style="display:flex;">
                <div class="modal-content" style="max-width: 450px;">
                    <h2 class="modal-header" style="color: #28a745;">Success!</h2>
                    <div class="modal-divider"></div>
                    <div class="modal-body">
                        <p>Lawyer Details Edited Successfully!</p>
                    </div>
                    <div class="modal-footer">
                        <a href="lawyers.php" class="modal-btn modal-btn-primary">OK</a>
                    </div>
                </div>
            </div>';
        }; };
    };

?>
</div>
<script>
    let currentLawyerId = null;
    let currentLawyerEmail = null;

    function showUnverifyConfirmModal(lawyerId, lawyerEmail) {
        // Set the values for the form
        document.getElementById('unverifyLawyerId').value = lawyerId;
        document.getElementById('unverifyLawyerEmail').value = lawyerEmail;
        
        // Show the modal
        document.getElementById('unverifyConfirmModal').style.display = 'flex';
    }

    function hideUnverifyConfirmModal() {
        document.getElementById('unverifyConfirmModal').style.display = 'none';
    }

    // Attach the event listener to the form itself, to be triggered on submission.
    // This simplifies the logic and removes the need for the JS form creation part.
    document.getElementById('unverifyConfirmModal').addEventListener('click', function(event) {
        if (event.target === this) {
            hideUnverifyConfirmModal();
        }
    });

</script>

</body>
</html>