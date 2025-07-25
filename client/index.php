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
        
    <title>Dashboard | SafeSpace PH</title>
    <style>
        .dashbord-tables{
            animation: transitionIn-Y-over 0.5s;
        }
        .filter-container{
            animation: transitionIn-Y-bottom  0.5s;
        }
        .sub-table,.anime{
            animation: transitionIn-Y-bottom 0.5s;
        }
         
         
        .dash-body {
            overflow-y: auto;
        }
         
    </style>
    
    
</head>
<body>
    <?php



    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='c'){
            header("location: ../login.php");
        }else{
            $useremail=$_SESSION["user"];
        }

    }else{
        header("location: ../login.php");
    }
    


    include("../connection.php");


    $clientid = $_SESSION['cid']; 
    $clientname = $_SESSION['cname']; // Retrieve clientname from session

    $userrow = $database->query("select * from client where cemail='$useremail'");
    $userfetch=$userrow->fetch_assoc();
    $userid= $userfetch["cid"];
    $username=$userfetch["cname"];




    
    ?>
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
                                    <p class="profile-title"><?php echo htmlspecialchars($clientname); ?></p>
                                    <p class="profile-subtitle"><?php echo $_SESSION['user']; ?></p>
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
                    <td class="menu-btn menu-icon-home menu-active menu-icon-home-active" >
                        <a href="index.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text menu-text-active">Home</p></a></div></a>
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
                    <td class="menu-btn menu-icon-lawyers">
                        <a href="lawyers.php" class="non-style-link-menu"><div><p class="menu-text">All Lawyers</p></a></div>
                    </td>
                </tr>
              
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></a></div>
                    </td>
                </tr>
                
            </table>
        </div>
        <div class="dash-body" style="margin-top: 15px">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;" >
                        
                        <tr >
                            
                            <td colspan="1" class="nav-bar" >
                            <p style="font-size: 23px;padding-left:12px;font-weight: 600;margin-left:20px;">Home</p>
                          
                            </td>
                            <td width="25%">

                            </td>
                            <td width="15%">
                                <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                                    Today's Date
                                </p>
                                <p class="heading-sub12" style="padding: 0;margin: 0;">
                                    <?php 
                                date_default_timezone_set('Asia/Kolkata');
        
                                $today = date('Y-m-d');
                                echo $today;


                                $clientrow = $database->query("select  * from  client;");
                                $lawyerrow = $database->query("select  * from  lawyer;");
                                $appointmentrow = $database->query("select  * from  appointment where appodate>='$today';");
                                $schedulerow = $database->query("select  * from  schedule where scheduledate='$today';");


                                ?>
                                </p>
                            </td>
                            <td width="10%">
                                <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                            </td>
        
        
                        </tr>
                <tr>
                    <td colspan="4" >
                        
                    <center>
                    <table class="filter-container lawyer-header client-header" style="border: none;width:95%" border="0" >
                    <tr>
                        <td >
                            <h3>Welcome!</h3>
                            <h1><?php echo $username  ?>.</h1>
                            <p>Haven't any idea about lawyers? no problem let's jumping to 
                                <a href="lawyers.php" class="non-style-link"><b>"All Lawyers"</b></a> section or 
                                <a href="schedule.php" class="non-style-link"><b>"Sessions"</b> </a><br>
                                Track your past and future appointments history.<br>Also find out the expected arrival time of your lawyer or medical consultant.<br><br>
                            </p>
                            
                            <h3>Channel a Lawyer Here</h3>
                            <form action="schedule.php" method="post" style="display: flex">

                                <input type="search" name="search" class="input-text " placeholder="Search Lawyer and We will Find The Session Available" list="lawyers" style="width:45%;">&nbsp;&nbsp;
                                
                                <?php
                                    echo '<datalist id="lawyers">';
                                    $list11 = $database->query("select  lawyername,lawyeremail from  lawyer;");
    
                                    for ($y=0;$y<$list11->num_rows;$y++){
                                        $row00=$list11->fetch_assoc();
                                        $d=$row00["lawyername"];
                                        
                                        echo "<option value='$d'><br/>";
                                        
                                    };
    
                                echo ' </datalist>';
    ?>
                                
                           
                                <input type="Submit" value="Search" class="login-btn btn-primary btn" style="padding-left: 25px;padding-right: 25px;padding-top: 10px;padding-bottom: 10px;">
                            
                            <br>
                            <br>
                            
                        </td>
                    </tr>
                    </table>
                    </center>
                    
                </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <table border="0" width="100%"">
                            <tr>
                                <td width="50%">

                                    


                                    <center>
                                        <table class="filter-container" style="border: none;" border="0">
                                            <tr>
                                                <td colspan="4">
                                                    <p style="font-size: 20px;font-weight:600;padding-left: 12px;">Status</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                
        <td style="width: 25%;">
            <div class="dashboard-items" style="padding: 21px 20px; margin: auto; width: 95%; display: flex; align-items: center;">
                <div style="flex-grow: 1;">
                    <div class="h1-dashboard">
                        <?php echo $lawyerrow->num_rows ?>
                    </div><br>
                    <div class="h3-dashboard">
                        All Lawyers
                    </div>
                </div>
                <div class="btn-icon-back dashboard-icons" style="background-image: url('../img/icons/lawyers-hover.svg'); width: 40px; height: 40px; background-size: contain; background-repeat: no-repeat; margin-left: 20px;"></div>
            </div>
        </td>

        
        <td style="width: 25%;">
            <div class="dashboard-items" style="padding: 21px 20px; margin: auto; width: 95%; display: flex; align-items: center;">
                <div style="flex-grow: 1;">
                    <div class="h1-dashboard">
                        <?php echo $clientrow->num_rows ?>
                    </div><br>
                    <div class="h3-dashboard">
                        All Clients
                    </div>
                </div>
                <div class="btn-icon-back dashboard-icons" style="background-image: url('../img/icons/clients-hover.svg'); width: 40px; height: 40px; background-size: contain; background-repeat: no-repeat; margin-left: 20px;"></div>
            </div>
        </td>
    </tr>

    <tr>
        
        <td style="width: 25%;">
            <div class="dashboard-items" style="padding: 21px 20px; margin: auto; width: 95%; display: flex; align-items: center;">
                <div style="flex-grow: 1;">
                    <div class="h1-dashboard">
                        <?php echo $appointmentrow->num_rows ?>
                    </div> <br>
                    <div class="h3-dashboard">
                        New Sessions
                        <br>
                    </div>
                </div>
                <div class="btn-icon-back dashboard-icons" style="background-image: url('../img/icons/newbookings-hover.svg'); width: 40px; height: 40px; background-size: contain; background-repeat: no-repeat; margin-left: 20px;"></div>
            </div>
        </td>

            
        <td style="width: 25%;">
            <div class="dashboard-items" style="padding: 21px 20px; margin: auto; width: 95%; display: flex; align-items: center;">
                <div style="flex-grow: 1;">
                    <div class="h1-dashboard">
                        <?php echo $schedulerow->num_rows ?>
                    </div> <br>
                    <div class="h3-dashboard">
                        Today's Sessions
                        <br>
                    </div>
                </div>
                <div class="btn-icon-back dashboard-icons" style="background-image: url('../img/icons/sessions-hover.svg'); width: 40px; height: 40px; background-size: contain; background-repeat: no-repeat; margin-left: 20px;"></div>
            </div>
        </td>
                                                
                                            </tr>
                                        </table>
                                    </center>







                                </td>
                                <td>


                            
                                    <p style="font-size: 20px;font-weight:600;padding-left: 40px;" class="anime">Your Upcoming Booking</p>
                                    <center>
                                        <div class="abc scroll" style="height: 250px;padding: 0;margin: 0;">
                                        <table width="85%" class="sub-table scrolldown" border="0" >
                                        <thead>
                                            
                                        <tr>
                                        <th class="table-headin">
                                                    
                                                
                                                    Appoint. Number
                                                    
                                                    </th>
                                                <th class="table-headin">
                                                    
                                                
                                                Session Title
                                                
                                                </th>
                                                
                                                <th class="table-headin">
                                                    Lawyer
                                                </th>
                                                <th class="table-headin">
                                                    
                                                    Sheduled Date & Time
                                                    
                                                </th>
                                                    
                                                </tr>
                                        </thead>
                                        <tbody>
                                        
                                            <?php
                                            $nextweek=date("Y-m-d",strtotime("+1 week"));
                                                $sqlmain= "select * from schedule inner join appointment on schedule.scheduleid=appointment.scheduleid inner join client on client.cid=appointment.cid inner join lawyer on schedule.lawyerid=lawyer.lawyerid  where  client.cid=$userid  and schedule.scheduledate>='$today' order by schedule.scheduledate asc";

                                                $result= $database->query($sqlmain);
                
                                                if($result->num_rows==0){
                                                    echo '<tr>
                                                    <td colspan="4">
                                                    <br><br><br><br>
                                                    <center>
                                                    <img src="../img/notfound.svg" width="25%">
                                                    
                                                    <br>
                                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">Nothing to show here!</p>
                                                    <a class="non-style-link" href="schedule.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Channel a Lawyer &nbsp;</font></button>
                                                    </a>
                                                    </center>
                                                    <br><br><br><br>
                                                    </td>
                                                    </tr>';
                                                    
                                                }
                                                else{
                                                for ( $x=0; $x<$result->num_rows;$x++){
                                                    $row=$result->fetch_assoc();
                                                    $scheduleid=$row["scheduleid"];
                                                    $title=$row["title"];
                                                    $apponum=$row["apponum"];
                                                    $lawyername=$row["lawyername"];
                                                    $scheduledate=$row["scheduledate"];
                                                    $scheduletime=$row["scheduletime"];
                                                   
                                                    echo '<tr>
                                                        <td style="padding:30px;font-size:25px;font-weight:700;"> &nbsp;'.
                                                        $apponum
                                                        .'</td>
                                                        <td style="padding:20px;"> &nbsp;'.
                                                        substr($title,0,30)
                                                        .'</td>
                                                        <td>
                                                        '.substr($lawyername,0,20).'
                                                        </td>
                                                        <td style="text-align:center;">
                                                            '.substr($scheduledate,0,10).' '.substr($scheduletime,0,5).'
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
                    </td>
                <tr>
            </table>
        </div>
    </div>


</body>
</html>
