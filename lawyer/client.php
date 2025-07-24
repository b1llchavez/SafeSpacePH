<?php
// Ensure all files are included correctly at the top.
session_start();

// Import database connection
include("../connection.php");

// Authentication: Check if the user is logged in and is a 'lawyer'
if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='l'){
        header("location: ../login.php");
        exit();
    }else{
        $useremail=$_SESSION["user"];
    }
}else{
    header("location: ../login.php");
    exit();
}

// Get Lawyer Info
$userrow = $database->query("select * from lawyer where lawyeremail='$useremail'");
$userfetch=$userrow->fetch_assoc();
$userid= $userfetch["lawyerid"];
$username=$userfetch["lawyername"];

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

    <title>Clients | SafeSpace PH</title>
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
        
        /* Consistent Modal Styling from lawyer_appointments.php */
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
            padding: 30px 50px;
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
            margin: 18px 0 28px 0;
        }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 25px;
        }
        .modal-btn {
            border: none;
            border-radius: 7px;
            padding: 12px 28px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
        }
        .modal-btn-soft {
            background: #f0e9f7;
            color: #5A2675;
        }
        .modal-btn-soft:hover {
            background: #e2d8fa;
        }
        
    
        
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 30px;
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
        }
        .detail-full {
            grid-column: 1 / -1;
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
                    <td class="menu-btn menu-icon-dashbord" >
                        <a href="index.php" class="non-style-link"><div><p class="menu-text">Dashboard</p></a></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-session">
                        <a href="manage-appointments.php" class="non-style-link-menu"><div><p class="menu-text">Share a Safe Space</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="lawyer_appointments.php" class="non-style-link-menu"><div><p class="menu-text">My Appointments</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-schedule">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">Manage Appointments</p></a></div>
                    </td>
                </tr>
               <tr class="menu-row" >
                    <td class="menu-btn menu-icon-client menu-active menu-icon-client-active">
                        <a href="client.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text menu-text-active"> My Clients</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></a></div>
                    </td>
                </tr>
            </table>
        </div>
        <?php       
            $selecttype="My";
            $current="My clients Only";
            if($_POST){
                if(isset($_POST["search"])){
                    $keyword=$_POST["search12"];
                    $sqlmain= "select * from client where cemail='$keyword' or cname='$keyword' or cname like '$keyword%' or cname like '%$keyword' or cname like '%$keyword%' ";
                    $selecttype="my";
                }
                
                if(isset($_POST["filter"])){
                    if($_POST["showonly"]=='all'){
                        $sqlmain= "select * from client";
                        $selecttype="All";
                        $current="All Clients";
                    }else{
                        $sqlmain= "select DISTINCT client.* from appointment inner join client on client.cid=appointment.cid inner join schedule on schedule.scheduleid=appointment.scheduleid where schedule.lawyerid=$userid;";
                        $selecttype="My";
                        $current="My Clients Only";
                    }
                }
            }else{
                $sqlmain= "select DISTINCT client.* from appointment inner join client on client.cid=appointment.cid inner join schedule on schedule.scheduleid=appointment.scheduleid where schedule.lawyerid=$userid;";
                $selecttype="My";
            }
        ?>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td>
                        <form action="" method="post" class="header-search">
                            <input type="search" name="search12" class="input-text header-searchbar" placeholder="Search Client name or Email" list="client">&nbsp;&nbsp;
                            
                            <?php
                                echo '<datalist id="client">';
                                $list11 = $database->query($sqlmain);
                                for ($y=0;$y<$list11->num_rows;$y++){
                                    $row00=$list11->fetch_assoc();
                                    $d=$row00["cname"];
                                    $c=$row00["cemail"];
                                    echo "<option value='$d'></option>";
                                    echo "<option value='$c'></option>";
                                };
                            echo ' </datalist>';
                            ?>
                            <input type="Submit" value="Search" name="search" class="login-btn btn-primary btn" style="padding-left: 25px;padding-right: 25px;padding-top: 10px;padding-bottom: 10px;">
                        </form>
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php 
                                date_default_timezone_set('Asia/Manila');
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
                    <td colspan="4" style="padding-top:10px;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)"><?php echo $selecttype." Clients (".$list11->num_rows.")"; ?></p>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top:0px;width: 100%;" >
                        <center>
                        <table class="filter-container" border="0" >
                        <form action="" method="post">
                        <td  style="text-align: right;">
                        Show Details About : &nbsp;
                        </td>
                        <td width="30%">
                        <select name="showonly" id="" class="box filter-container-items" style="width:90% ;height: 37px;margin: 0;" >
                                    <option value="" disabled selected hidden><?php echo $current   ?></option><br/>
                                    <option value="my">My Clients Only</option><br/>
                                    <option value="all">All Clients</option><br/>
                        </select>
                    </td>
                    <td width="12%">
                        <input type="submit"  name="filter" value=" Filter" class=" btn-primary-soft btn button-icon btn-filter"  style="padding: 15px; margin :0;width:100%">
                        </form>
                    </td>
                    </tr>
                            </table>
                        </center>
                    </td>
                </tr>
                <tr>
                   <td colspan="4">
                       <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown"  style="border-spacing:0;">
                        <thead>
                        <tr>
                                <th class="table-headin">Name</th>
                                <th class="table-headin">Telephone</th>
                                <th class="table-headin">Email</th>
                                <th class="table-headin">Date of Birth</th>
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
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We couldn\'t find anything related to your keywords!</p>
                                    <a class="non-style-link" href="client.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Clients &nbsp;</button>
                                    </a>
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                    </tr>';
                                }
                                else{
                                for ( $x=0; $x<$result->num_rows;$x++){
                                    $row=$result->fetch_assoc();
                                    $cid=$row["cid"];
                                    $name=$row["cname"];
                                    $email=$row["cemail"];
                                    $dob=$row["cdob"];
                                    $tel=$row["ctel"];
                                    
                                    echo '<tr>
                                        <td> &nbsp;'.htmlspecialchars(substr($name,0,35)).'</td>
                                        <td>'.htmlspecialchars(substr($tel,0,10)).'</td>
                                        <td>'.htmlspecialchars(substr($email,0,20)).'</td>
                                        <td>'.htmlspecialchars(substr($dob,0,10)).'</td>
                                        <td>
                                        <div style="display:flex;justify-content: center;">
                                            <a href="?action=view&id='.$cid.'" class="non-style-link">
                                                <button class="btn-primary-soft btn button-icon btn-view" style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;">
                                                    <font class="tn-in-text">View</font>
                                                </button>
                                            </a>
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
    if(isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])){
        $id=$_GET["id"];
        $sqlmain= "select * from client where cid='$id'";
        $result= $database->query($sqlmain);
        if($result->num_rows > 0) {
            $row=$result->fetch_assoc();
            $name=$row["cname"];
            $email=$row["cemail"];
            $dob=$row["cdob"];
            $tele=$row["ctel"];
            $address=$row["caddress"];

            echo '
            <div id="viewModal" class="overlay active">
                <div class="modal-content">
                    <h2 class="modal-header">Client Details</h2>
                    <div class="modal-divider"></div>
                    <div class="modal-body">
                        <div class="detail-grid">
                            <div class="detail-item"><strong>Client ID:</strong> <span>P-'.htmlspecialchars($id).'</span></div>
                            <div class="detail-item"><strong>Date of Birth:</strong> <span>'.date("F j, Y", strtotime($dob)).'</span></div>
                            <div class="detail-item"><strong>Name:</strong> <span>'.htmlspecialchars($name).'</span></div>
                            <div class="detail-item"><strong>Email:</strong> <span>'.htmlspecialchars($email).'</span></div>
                            <div class="detail-item"><strong>Telephone:</strong> <span>'.htmlspecialchars($tele).'</span></div>
                            <div class="detail-full"><strong>Address:</strong> <span>'.htmlspecialchars($address).'</span></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="client.php" class="non-style-link"><button type="button" class="modal-btn modal-btn-soft">OK</button></a>
                    </div>
                </div>
            </div>';
        }
    }
    ?>

    <script>
        const viewModal = document.getElementById('viewModal');
        // Close modal if clicked outside of the content area
        window.onclick = function(event) {
            if (event.target == viewModal) {
                // Redirect to close the view modal by removing GET params
                window.location.href = 'client.php';
            }
        }
    </script>
</body>
</html>