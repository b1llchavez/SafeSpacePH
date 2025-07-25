<?php
    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }
    }else{
        header("location: ../login.php");
    }
    
    include("../connection.php");
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

    <title>Appointments | SafeSpace PH</title>
    <style>
        .popup {
            animation: transitionIn-Y-bottom 0.5s;
        }

        .sub-table {
            animation: transitionIn-Y-bottom 0.5s;
        }
        
        /* Filter Section Styling */
        .filter-container {
            display: inline-flex;
            gap: 10px;
            align-items: center;
            margin-right: 45px;
        }

        .filter-container-items {
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn-filter {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 15px;
            font-weight: 600;
            background: #f0e9f7;
            color: #5A2675;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-filter:hover {
            background: #e2d8fa;
        }

        /* Modal Window Improvements */
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
            padding: 20px 35px;
            width: 95%;
            position: relative;
            animation: fadeIn 0.3s;
            margin: 20px;
        }

        /* Specific size adjustments for different modals */
        .modal-content.add-session {
            max-width: 600px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }

        .modal-content.view-details {
            max-width: 800px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }

        .modal-header {
            text-align: center;
            color: #391053;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            margin-top: 0;
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
            padding: 0 5px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Input styling improvements */
        .input-text, .box {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ccc;
            border-radius: 7px;
            font-size: 15px;
            transition: border-color 0.2s;
        }

        .input-text:focus, .box:focus {
            border-color: #5A2675;
            box-shadow: 0 0 0 2px rgba(157, 114, 179, 0.2);
            outline: none;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #391053;
        }

        .add-new-form .form-group{
            margin-bottom: 12px;
        }

        /* Modal Button Styling */
        .modal-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-btn-soft {
            background: #f0e9f7;
            color: #5A2675;
        }

        .modal-btn-soft:hover {
            background: #e2d8fa;
        }

        .modal-btn-primary {
            background: #5A2675;
            color: white;
        }

        .modal-btn-primary:hover {
            background: #391053;
        }

        .modal-btn-danger {
            background: #dc3545;
            color: white;
        }

        .modal-btn-danger:hover {
            background: #bb2d3b;
        }

        /* Modal Footer Alignment */
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Center alignment for confirmation modals */
        .modal-footer[style*="justify-content: center"] {
            justify-content: center !important;
        }

        /* Form Buttons Layout */
        .add-new-form .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .add-new-form .modal-btn {
            min-width: 100px;
        }

        .btn-reset {
    /* Inherits styles from .btn-primary-soft and .btn */
    /* Add transition for smooth hover effect */
    transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
}

/* Hover styles for the Reset button, matching .btn-primary-soft:hover */
.btn-reset:hover {
    background-color: var(--primarycolor); /* This will be #5A2675 */
    color: #fff;
    box-shadow: 0 3px 5px 0 rgba(57, 108, 240, 0.3);
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
                                    <p class="profile-title">Administrator</p>
                                    <p class="profile-subtitle">admin@safespaceph.com</p>
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
                    <td class="menu-btn menu-icon-appoinment menu-active menu-icon-appoinment-active">
                        <a href="appointment.php" class="non-style-link-menu non-style-link-menu-active">
                            <div>
                                <p class="menu-text menu-text-active">Appointments</p>
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
                    <td class="menu-btn menu-icon-lawyers">
                        <a href="lawyers.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">All Lawyers</p>
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
                    <td width="50%">
                        <p style="margin-left: 45px; font-size: 23px;font-weight: 600;">Appointment Manager</p>
                    </td>
                    <td style="text-align: right;">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php 
                                date_default_timezone_set('Asia/Manila');
                                $today = date('Y-m-d');
                                echo $today;
                                $list110 = $database->query("select  * from  appointment;");
                            ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>
                
                <tr>
                    <td width="50%" style="padding-top:10px;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">All Appointments (<?php echo $list110->num_rows; ?>)</p>
                    </td>
                    <td style="padding-top:10px; text-align: right; padding-right: 45px;" colspan="2">
                        <form action="" method="post" style="display: inline-flex; gap: 10px; align-items: center;">
                            <input type="date" name="sheduledate" id="date" class="input-text" 
                                style="width: auto; padding: 8px 10px;" 
                                value="<?php echo isset($_POST['sheduledate']) ? htmlspecialchars($_POST['sheduledate']) : '' ?>">
                            
                            <select name="lawyerid" class="box" style="width: 200px; height: 42px; padding: 8px 10px;">
                                <option value="" disabled <?php if (!isset($_POST['lawyerid'])) echo 'selected'; ?> hidden>Choose Lawyer Name</option>
                                <?php
                                $list11 = $database->query("select * from lawyer order by lawyername asc;");
                                $selected_lawyer = isset($_POST['lawyerid']) ? $_POST['lawyerid'] : '';
                                
                                for ($y = 0; $y < $list11->num_rows; $y++) {
                                    $row00 = $list11->fetch_assoc();
                                    $sn = $row00["lawyername"];
                                    $id00 = $row00["lawyerid"];
                                    $selected = ($id00 == $selected_lawyer) ? "selected" : "";
                                    echo "<option value='" . $id00 . "' " . $selected . ">" . htmlspecialchars($sn) . "</option>";
                                }
                                ?>
                            </select>

                            <button type="submit" name="filter" class="btn-primary-soft btn" 
                                style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 15px; font-weight: 600;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                </svg>
                                Filter
                            </button>

                             <a href="appointment.php" class="non-style-link btn-primary-soft btn btn-reset" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 15px; font-weight: 600;">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
    Reset
</a>
                        </form>
                    </td>
                </tr>

                
                <?php
                    $sqlmain= "select appointment.appoid,schedule.scheduleid,schedule.title,lawyer.lawyername,client.cname,schedule.scheduledate,schedule.scheduletime,appointment.apponum,appointment.appodate from schedule inner join appointment on schedule.scheduleid=appointment.scheduleid inner join client on client.cid=appointment.cid inner join lawyer on schedule.lawyerid=lawyer.lawyerid";
                    if($_POST){
                        $sqlpt1="";
                        if(!empty($_POST["sheduledate"])){
                            $sheduledate=$_POST["sheduledate"];
                            $sqlpt1=" schedule.scheduledate='$sheduledate' ";
                        }

                        $sqlpt2="";
                        if(!empty($_POST["lawyerid"])){
                            $lawyerid=$_POST["lawyerid"];
                            $sqlpt2=" lawyer.lawyerid=$lawyerid ";
                        }

                        $sqllist=array($sqlpt1,$sqlpt2);
                        $sqlkeywords=array(" where "," and ");
                        $key2=0;
                        foreach($sqllist as $key){
                            if(!empty($key)){
                                $sqlmain.=$sqlkeywords[$key2].$key;
                                $key2++;
                            };
                        };
                    }else{
                        $sqlmain.=" order by schedule.scheduledate desc";
                    }
                ?>
                  
                <tr>
                   <td colspan="4">
                       <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0">
                        <thead>
                        <tr>
                                <th class="table-headin">
                                    Client name
                                </th>
                                <th class="table-headin">
                                    Appointment number
                                </th>
                                <th class="table-headin">
                                    Lawyer
                                </th>
                                <th class="table-headin">
                                    Session Title
                                </th>
                                <th class="table-headin" style="font-size:10px">
                                    Session Date & Time
                                </th>
                                <th class="table-headin">
                                    Appointment Date
                                </th>
                                <th class="table-headin">
                                    Events
                                </tr>
                        </thead>
                        <tbody>
                        
                            <?php
                                $result= $database->query($sqlmain);

                                if($result->num_rows==0){
                                    echo '<tr>
                                    <td colspan="7">
                                    <br><br><br><br>
                                    <center>
                                    <img src="../img/notfound.svg" width="25%">
                                    
                                    <br>
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We couldn\'t find anything related to your keywords!</p>
                                    <a class="non-style-link" href="appointment.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Appointments &nbsp;</font></button>
                                    </a>
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                    </tr>';
                                    
                                }
                                else{
                                for ( $x=0; $x<$result->num_rows;$x++){
                                    $row=$result->fetch_assoc();
                                    $appoid=$row["appoid"];
                                    $cname=$row["cname"];
                                    $apponum=$row["apponum"];
                                    $lawyername=$row["lawyername"];
                                    $title=$row["title"];
                                    $scheduledate=$row["scheduledate"];
                                    $scheduletime=$row["scheduletime"];
                                    $appodate=$row["appodate"];
                                    echo '<tr >
                                        <td style="font-weight:600;"> &nbsp;'.
                                        
                                        substr($cname,0,25)
                                        .'</td >
                                        <td style="text-align:center;font-size:23px;font-weight:500; color: var(--btnnicetext);">
                                        '.$apponum.'
                                        
                                        </td>
                                        <td>
                                        '.substr($lawyername,0,25).'
                                        </td>
                                        <td>
                                        '.substr($title,0,15).'
                                        </td>
                                        <td style="text-align:center;font-size:12px;">
                                            '.substr($scheduledate,0,10).' <br>'.substr($scheduletime,0,5).'
                                        </td>
                                        
                                        <td style="text-align:center;">
                                            '.$appodate.'
                                        </td>

                                        <td>
                                        <div style="display:flex;justify-content: center;">
                                       <a href="?action=drop&id='.$appoid.'&name='.urlencode($cname).'&session='.urlencode($title).'&apponum='.$apponum.'" class="non-style-link"><button  class="btn-primary-soft btn button-icon btn-delete"  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Cancel</font></button></a>
                                       &nbsp;&nbsp;&nbsp;</div>
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
    
    if(isset($_GET['action'])){
        $id=$_GET["id"];
        $action=$_GET["action"];
        $overlay_class = 'overlay active'; // Common class to show modal

        if($action=='drop'){
            $nameget=urldecode($_GET["name"]);
            $apponum=$_GET["apponum"];
            echo '
            <div id="deleteModal" class="'.$overlay_class.'">
                <div class="modal-content" style="max-width: 500px;">
                     <h2 class="modal-header">Are you sure?</h2>
                     <div class="modal-divider"></div>
                     <div class="modal-body" style="text-align: center;">
                        <p>You want to cancel this appointment for<br>Client: <strong>' . htmlspecialchars($nameget) . '</strong><br>Appointment No: <strong>' . htmlspecialchars($apponum) . '</strong></p>
                        <p style="font-size: 13px; color: #dc3545; margin-top: 15px;">This action cannot be undone.</p>
                     </div>
                     <div class="modal-footer">
                        <a href="appointment.php" class="non-style-link">
                            <button type="button" class="modal-btn modal-btn-soft">No</button>
                        </a>
                        <a href="delete-appointment.php?id='.$id.'" class="non-style-link">
                            <button type="button" class="modal-btn modal-btn-danger">Yes, Cancel</button>
                        </a>
                     </div>
                </div>
            </div>'; 
        }
    }

    ?>
    </div>

</body>
</html>
