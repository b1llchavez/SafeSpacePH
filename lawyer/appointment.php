<?php
session_start();

include("../connection.php");
require_once('../send_email.php'); 

if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'l') {
        header("location: ../login.php");
    } else {
        $useremail = $_SESSION["user"];
    }
} else {
    header("location: ../login.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_submit'])) {
    $appoid = $_POST['appoid'];
    $reason = $_POST['reason'];
    $explanation = trim($_POST['explanation']);
    $cancellation_reason_text = '';

    if ($reason === 'Other') {
        $cancellation_reason_text = trim($_POST['other_reason']);
    } else {
        $cancellation_reason_text = $reason;
    }

    $stmt_details = $database->prepare("
        SELECT
            c.cname,
            c.cemail,
            s.scheduledate,
            s.scheduletime,
            s.title,
            l.lawyername
        FROM appointment a
        JOIN client c ON a.cid = c.cid
        JOIN schedule s ON a.scheduleid = s.scheduleid
        JOIN lawyer l ON s.lawyerid = l.lawyerid
        WHERE a.appoid = ?
    ");
    $stmt_details->bind_param("i", $appoid);
    $stmt_details->execute();
    $result_details = $stmt_details->get_result();
    $details = $result_details->fetch_assoc();
    $stmt_details->close();

    if ($details) {
        $recipientEmail = $details['cemail'];
        $recipientName = $details['cname'];
        $appointmentDetails = [
            'lawyerName' => $details['lawyername'],
            'appointmentDate' => $details['scheduledate'],
            'appointmentTime' => $details['scheduletime'],
            'caseTitle' => $details['title']
        ];
        
        sendDetailedAppointmentCanceledEmail(
            $recipientEmail,
            $recipientName,
            $appointmentDetails,
            $cancellation_reason_text,
            $explanation
        );

        $status = 'cancelled';
        $stmt_update = $database->prepare("UPDATE appointment SET status=?, cancellation_reason=?, cancellation_explanation=? WHERE appoid=?");
        $stmt_update->bind_param("sssi", $status, $cancellation_reason_text, $explanation, $appoid);
        $stmt_update->execute();
        $stmt_update->close();

        header("Location: appointment.php?action=cancelled");
        exit;
    } else {
        header("Location: appointment.php?action=error");
        exit;
    }
}

$userrow = $database->query("select * from lawyer where lawyeremail='$useremail'");
$userfetch = $userrow->fetch_assoc();
$userid = $userfetch["lawyerid"];
$username = $userfetch["lawyername"];

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

    <title>Appointments | SafeSpace PH</title>
    <style>
        .popup {
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table {
            animation: transitionIn-Y-bottom 0.5s;
        }
        .dash-body {
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
            padding: 40px 50px;
            max-width: 600px;
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
        .modal-body .form-group {
            margin-bottom: 20px;
        }
        .modal-body .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #391053;
            font-size: 1.05rem;
        }
        .modal-body .radio-group label {
            font-weight: 500;
            font-size: 1rem;
            color: #3a2c5c;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }
        .modal-body input[type="radio"] {
            accent-color: #5A2675;
            width: 18px;
            height: 18px;
        }
        .modal-body .input-text, .modal-body textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ccc;
            border-radius: 7px;
            font-size: 15px;
            transition: border-color 0.2s;
        }
        .modal-body .input-text:focus, .modal-body textarea:focus {
            border-color: #5A2675;
            box-shadow: 0 0 0 2px rgba(157, 114, 179, 0.2);
            outline: none;
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
            transition: background 0.2s, box-shadow 0.2s;
        }
        .modal-btn-primary {
            background: #5A2675;
            color: #fff;
        }
        .modal-btn-primary:hover {
            background: #391053;
            box-shadow: 0 2px 8px rgba(90, 38, 117, 0.18);
        }
        .modal-btn-soft {
            background: #f0e9f7;
            color: #5A2675;
        }
        .modal-btn-soft:hover {
            background: #e2d8fa;
        }
        #other_reason_input {
            display: none;
            margin-top: 10px;
            padding-left: 26px;
        }
        #cancel-error {
            color: #d9534f;
            text-align: center;
            display: none;
            margin-bottom: 15px;
            font-weight: 500;
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
                    <td class="menu-btn menu-icon-dashbord" >
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Dashboard</p></a></div></a>
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
                    <td class="menu-btn menu-icon-schedule menu-active menu-icon-schedule-active">
                        <a href="appointment.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text menu-text-active">Manage Appointments</p></a></div>
                    </td>
                </tr>
               <tr class="menu-row" >
                    <td class="menu-btn menu-icon-client">
                        <a href="client.php" class="non-style-link-menu"><div><p class="menu-text"> My Clients</p></a></div>
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
                <tr>
                    <td style="padding-left: 45px; vertical-align: bottom;">
                        <p style="font-size: 23px;font-weight: 600;">Manage Appointments</p>
                        <?php
                            $list110 = $database->query("select * from schedule inner join appointment on schedule.scheduleid=appointment.scheduleid inner join client on client.cid=appointment.cid inner join lawyer on schedule.lawyerid=lawyer.lawyerid  where  lawyer.lawyerid=$userid ");
                        ?>
                        <p class="heading-main12" style="font-size:18px;color:rgb(49, 49, 49)">All Appointments (<?php echo $list110->num_rows; ?>)</p>
                    </td>
                    <td style="padding-right: 45px; text-align: right; vertical-align: bottom;">
                        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 15px; margin-bottom: 10px;">
                            <div style="text-align: right;">
                                <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;">
                                    Today's Date
                                </p>
                                <p class="heading-sub12" style="padding: 0;margin: 0;">
                                    <?php
                                    date_default_timezone_set('Asia/Manila');
                                    echo date('Y-m-d');
                                    ?>
                                </p>
                            </div>
                            <button class="btn-label" style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                        </div>
                        <form action="" method="post" style="display: inline-flex; gap: 10px; align-items: center;">
                             <input type="date" name="sheduledate" id="date" class="input-text" style="width: auto; padding: 8px 10px;" value="<?php echo isset($_POST['sheduledate']) ? htmlspecialchars($_POST['sheduledate']) : '' ?>">

                             <button type="submit" name="filter" class="btn-primary-soft btn" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 15px; font-weight: 600;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                Filter
                             </button>

                             <a href="appointment.php" class="non-style-link btn-primary-soft btn" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 15px; font-weight: 600;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                                Reset
                             </a>
                        </form>
                    </td>
                </tr>
                <?php
                $sqlmain = "select appointment.appoid,schedule.scheduleid,schedule.title,lawyer.lawyername,client.cname,schedule.scheduledate,schedule.scheduletime,appointment.apponum,appointment.appodate,appointment.status from schedule inner join appointment on schedule.scheduleid=appointment.scheduleid inner join client on client.cid=appointment.cid inner join lawyer on schedule.lawyerid=lawyer.lawyerid  where  lawyer.lawyerid=$userid ";

                if ($_POST) {
                    if (!empty($_POST["sheduledate"])) {
                        $sheduledate = $_POST["sheduledate"];
                        $sqlmain .= " and schedule.scheduledate='$sheduledate' ";
                    }
                }
                ?>
                <tr>
                    <td colspan="2" style="padding-top:20px;">
                        <center>
                            <div class="abc scroll">
                                <table width="93%" class="sub-table scrolldown" border="0">
                                    <thead>
                                        <tr>
                                            <th class="table-headin">Client Name</th>
                                            <th class="table-headin">Appointment Number</th>
                                            <th class="table-headin">Session Title</th>
                                            <th class="table-headin">Session Date & Time</th>
                                            <th class="table-headin">Appointment Date</th>
                                            <th class="table-headin">Status</th>
                                            <th class="table-headin">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = $database->query($sqlmain);
                                        if ($result->num_rows == 0) {
                                            echo '<tr>
                                            <td colspan="7">
                                            <br><br><br><br>
                                            <center>
                                            <img src="../img/notfound.svg" width="25%">
                                            <br>
                                            <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We couldn\'t find anything related to your keywords!</p>
                                            <a class="non-style-link" href="appointment.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Appointments &nbsp;</button></a>
                                            </center>
                                            <br><br><br><br>
                                            </td>
                                            </tr>';
                                        } else {
                                            for ($x = 0; $x < $result->num_rows; $x++) {
                                                $row = $result->fetch_assoc();
                                                $appoid = $row["appoid"];
                                                $cname = $row["cname"];
                                                $apponum = $row["apponum"];
                                                $title = $row["title"];
                                                $scheduledate = $row["scheduledate"];
                                                $scheduletime = $row["scheduletime"];
                                                $appodate = $row["appodate"];
                                                $status = $row["status"];
                                                echo '<tr>
                                                    <td style="font-weight:600;"> &nbsp;' . substr($cname, 0, 25) . '</td>
                                                    <td style="text-align:center;font-size:23px;font-weight:500; color: var(--btnnicetext);">' . $apponum . '</td>
                                                    <td>' . substr($title, 0, 25) . '</td>
                                                    <td style="text-align:center;">' . substr($scheduledate, 0, 10) . ' @' . substr($scheduletime, 0, 5) . '</td>
                                                    <td style="text-align:center;">' . $appodate . '</td>
                                                    <td style="text-align:center;">' . ucfirst($status) . '</td>
                                                    <td>
                                                    <div style="display:flex;justify-content: center;">';
                                                
                                                if ($status == 'cancelled') {
                                                    echo '<button class="btn-primary-soft btn" style="padding:10px 15px; border-color: #999; color: #999; cursor:not-allowed;" disabled>Cancelled</button>';
                                                } else {
                                                    echo '<button class="btn-primary-soft btn button-icon btn-delete" onclick="openCancelModal(' . $appoid . ')" style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Cancel</font></button>';
                                                }
                                                
                                                echo '</div>
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

    <div id="cancelModal" class="overlay">
        <div class="modal-content">
            <h2 class="modal-header">Cancel Appointment Confirmation</h2>
            <div class="modal-divider"></div>
            <div class="modal-body">
                <form id="cancelForm" action="appointment.php" method="POST" onsubmit="return validateCancelForm()">
                    <input type="hidden" name="appoid" id="modal_appoid">
                    <input type="hidden" name="cancel_submit" value="1">
                    
                    <p id="cancel-error"></p>

                    <div class="form-group">
                        <label>Please select a reason for cancellation:</label>
                        <div class="radio-group">
                            <label><input type="radio" name="reason" value="Change of time" onclick="toggleOtherReason()"> Change of time</label>
                            <label><input type="radio" name="reason" value="Personal emergency" onclick="toggleOtherReason()"> Personal emergency</label>
                            <label><input type="radio" name="reason" value="Incomplete details" onclick="toggleOtherReason()"> Incomplete details from client</label>
                            <label><input type="radio" id="reason_other" name="reason" value="Other" onclick="toggleOtherReason()"> Other</label>
                        </div>
                        <div id="other_reason_input">
                            <input type="text" id="other_reason_text" name="other_reason" class="input-text" placeholder="Please specify...">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="explanation">Optional Description/Explanation:</label>
                        <textarea name="explanation" class="input-text" style="height: 100px; resize: vertical;" placeholder="Provide more details for the client..."></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="modal-btn modal-btn-soft" onclick="closeCancelModal()">Back</button>
                        <button type="submit" class="modal-btn modal-btn-primary">Confirm Cancellation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const cancelModal = document.getElementById('cancelModal');

        function openCancelModal(appoid) {
            document.getElementById('modal_appoid').value = appoid;
            cancelModal.classList.add('active');
        }

        function closeCancelModal() {
            cancelModal.classList.remove('active');
            document.getElementById('cancelForm').reset();
            document.getElementById('cancel-error').style.display = 'none';
            document.getElementById('other_reason_input').style.display = 'none';
        }

        function toggleOtherReason() {
            if (document.getElementById('reason_other').checked) {
                document.getElementById('other_reason_input').style.display = 'block';
            } else {
                document.getElementById('other_reason_input').style.display = 'none';
            }
        }

        function validateCancelForm() {
            const errorP = document.getElementById('cancel-error');
            errorP.style.display = 'none';
            
            const reasons = document.getElementsByName('reason');
            let reason_checked = false;
            for (let i = 0; i < reasons.length; i++) {
                if (reasons[i].checked) {
                    reason_checked = true;
                    break;
                }
            }

            if (!reason_checked) {
                errorP.innerText = 'Please select a reason for cancellation.';
                errorP.style.display = 'block';
                return false;
            }

            if (document.getElementById('reason_other').checked) {
                if (document.getElementById('other_reason_text').value.trim() === '') {
                    errorP.innerText = 'Please specify the "Other" reason.';
                    errorP.style.display = 'block';
                    return false;
                }
            }

            return true;
        }

        window.onclick = function(event) {
            if (event.target == cancelModal) {
                closeCancelModal();
            }
        }
    </script>
</body>
</html>