<?php
session_start();

if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'a') {
        header("location: ../login.php");
    }
} else {
    header("location: ../login.php");
}

include("../connection.php");

// Handler for appointment rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'reject') {
    if (isset($_POST['appoid']) && !empty($_POST['appoid']) && isset($_POST['rejection_reason'])) {
        $appoid = $_POST['appoid'];
        $reason = $_POST['rejection_reason'];
        if ($reason === 'Other') {
            if (isset($_POST['other_reason_text']) && !empty(trim($_POST['other_reason_text']))) {
                $reason = 'Other: ' . htmlspecialchars(trim($_POST['other_reason_text']));
            } else {
                // Redirect with an error if 'Other' is selected but no text is provided
                header("Location: schedule.php?action=reject_error&reason=other_empty");
                exit();
            }
        }
        $description = isset($_POST['rejection_description']) ? htmlspecialchars(trim($_POST['rejection_description'])) : '';

        // Update the appointment status to 'rejected'
        $stmt = $database->prepare("UPDATE appointment SET status = 'rejected' WHERE appoid = ?");
        $stmt->bind_param("i", $appoid);
        $stmt->execute();
        $stmt->close();

        // Redirect to show a success message
        header("Location: schedule.php?action=reject_success");
        exit();
    } else {
        // Redirect if essential data is missing
        header("Location: schedule.php?action=reject_error&reason=missing_data");
        exit();
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

    <title>Schedule | SafeSpace PH</title>
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
            font-family:inherit;
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
        
        /* STYLES FROM MANAGE-APPOINTMENTS.PHP */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            align-items: center;
            justify-content: center;
        }

        .modal-content-req {
            background-color: #fefefe;
            margin: auto;
            padding: 25px;
            border: 1px solid #888;
            width: 90%;
            max-width: 500px;
            border-radius: 10px;
            animation: transitionIn-Y-bottom 0.5s;
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            border: none;
            background: transparent;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.2s, color 0.2s;
            text-decoration: none;
            z-index: 1;
        }

        .close-btn:hover {
            background-color: #f0e9f7;
            color: #5A2675;
        }

        .rejection-reason {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .rejection-reason input[type="radio"] {
            margin-right: 10px;
        }

        .rejection-reason label {
            flex-grow: 1;
        }

        .action-btn-container {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .details-label {
            font-weight: bold;
            color: #555;
            margin-top: 10px;
        }

        .details-data {
            background-color: #f1f1f1;
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 10px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .button-icon img {
            width: 15px;
            height: auto;
            vertical-align: middle;
            margin-right: 8px;
        }

        .menu-btn {
            padding: 2px;
            background-position: 30% 50%;
            background-repeat: no-repeat;
            transition: 0.5s;
        }
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
                                <td width="30%" style="padding-left:20px">
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
                    <td class="menu-btn menu-icon-schedule menu-active menu-icon-schedule-active">
                        <a href="schedule.php" class="non-style-link-menu non-style-link-menu-active">
                            <div>
                                <p class="menu-text menu-text-active">Session Requests</p>
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

        <div class="dash-body" style="overflow-y: auto; overflow-x: hidden;">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr>
                    <td colspan="2">
                        <p style="margin-left: 45px; font-size: 23px;font-weight: 600;">Session Requests Manager</p>
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
                            ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button class="btn-label" style="display: flex;justify-content: center;align-items: center;"><img
                                src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>

                <tr>
                    <td colspan="4">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">New Session Requests</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <center>
                            <?php
                            if (isset($_GET['action'])) {
                                if ($_GET['action'] == 'reject_success') {
                                    echo "<div style='padding: 10px; margin: 10px 40px; border-radius: 5px; background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba;'>Appointment has been successfully rejected.</div>";
                                }
                            }
                            ?>
                        </center>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-bottom: 30px;">
                        <center>
                            <div class="abc scroll">
                                <table width="95%" class="sub-table scrolldown" border="0">
                                    <thead>
                                        <tr>
                                            <th class="table-headin">Client Name</th>
                                            <th class="table-headin">Session Title</th>
                                            <th class="table-headin">Preferred Date & Time</th>
                                            <th class="table-headin">Requested On</th>
                                            <th class="table-headin" style="width: 20%;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql_new_requests = "SELECT 
                                            appointment.appoid,
                                            appointment.appodate,
                                            appointment.description AS case_description,
                                            schedule.scheduleid,
                                            schedule.title,
                                            schedule.scheduledate,
                                            schedule.scheduletime,
                                            client.cname
                                        FROM appointment 
                                        INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid
                                        INNER JOIN client ON appointment.cid = client.cid
                                        WHERE schedule.lawyerid IS NULL AND appointment.status = 'pending' 
                                        ORDER BY schedule.scheduledate ASC, schedule.scheduletime ASC";

                                        $result_new_requests = $database->query($sql_new_requests);

                                        if ($result_new_requests->num_rows == 0) {
                                            echo '<tr>
                                                <td colspan="5">
                                                    <br><br><br><br>
                                                    <center>
                                                        <img src="../img/notfound.svg" width="25%">
                                                        <br>
                                                        <p class="heading-main12" style="font-size:20px;color:rgb(49, 49, 49)">No new session requests found!</p>
                                                    </center>
                                                    <br><br><br><br>
                                                </td>
                                            </tr>';
                                        } else {
                                            while ($row_request = $result_new_requests->fetch_assoc()) {
                                                $appoid = $row_request["appoid"];
                                                $appodate = $row_request["appodate"];
                                                $scheduleid = $row_request["scheduleid"];
                                                $title = $row_request["title"];
                                                $scheduledate = $row_request["scheduledate"];
                                                $scheduletime = $row_request["scheduletime"];
                                                $clientname = $row_request["cname"];
                                                $case_description = $row_request["case_description"];

                                                echo '<tr>
                                                    <td>' . htmlspecialchars($clientname) . '</td>
                                                    <td>' . htmlspecialchars($title) . '</td>
                                                    <td style="text-align:center;">' . date("M d, Y", strtotime($scheduledate)) . '<br>' . date("g:i A", strtotime($scheduletime)) . '</td>
                                                    <td style="text-align:center;">' . date("M d, Y", strtotime($appodate)) . '</td>
                                                    <td>
                                                        <div class="action-btn-container">
                                                            <button class="btn-primary-soft btn button-icon btn-view"
                                                                data-clientname="' . htmlspecialchars($clientname) . '"
                                                                data-title="' . htmlspecialchars($title) . '"
                                                                data-date="' . date("F j, Y", strtotime($scheduledate)) . '"
                                                                data-time="' . date("g:i A", strtotime($scheduletime)) . '"
                                                                data-description="' . htmlspecialchars($case_description) . '">
                                                               View
                                                            </button>
                                                            <a href="#" class="non-style-link reject-btn" data-appoid="' . $appoid . '">
                                                                <button type="button" class="btn-primary-soft btn button-icon btn-delete">
                                                                    Reject
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
                <tr>
                    <td width="50%" style="padding-top:10px;">
                        <?php
                            $list110 = $database->query("select  * from  schedule;");
                        ?>
                      

    <div id="viewDetailsModal" class="modal">
        <div class="modal-content-req">
            <span class="close-btn">&times;</span>
            <h3 style="text-align:center; color:#391053; font-size:1.8rem; font-weight:700; margin:0 0 10px 0; letter-spacing:0.5px;">
                Session Request Details
            </h3>
            <div style="width:100%; height:3px; background:linear-gradient(90deg, #391053 0%, #5A2675 30%, #9D72B3 65%, #C9A8F1 100%); border-radius:2px; margin:18px 0 28px 0;"></div>
            <div>
                <p class="details-label">Client Name:</p>
                <p id="detailClientName" class="details-data"></p>
                <p class="details-label">Session Title:</p>
                <p id="detailSessionTitle" class="details-data"></p>
                <p class="details-label">Preferred Date & Time:</p>
                <p id="detailDateTime" class="details-data"></p>
                <p class="details-label">Case Description:</p>
                <p id="detailDescription" class="details-data" style="max-height: 150px; overflow-y: auto;"></p>
            </div>
        </div>
    </div>

    <div id="rejectionModal" class="modal">
        <div class="modal-content-req">
            <span class="close-btn">&times;</span>
            <h2 style="margin-bottom: 15px;">Reason for Rejection</h2>
            <form id="rejectionForm" action="schedule.php" method="POST" novalidate>
                <input type="hidden" name="action" value="reject">
                <input type="hidden" id="rejectAppoId" name="appoid" value="">
                <div id="rejectionError" style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 15px; display: none;"></div>
                <p style="margin-bottom: 15px;">Why do you want to reject this appointment?</p>
                <div class="rejection-reason">
                    <input type="radio" id="reason_time" name="rejection_reason" value="Change of time" required>
                    <label for="reason_time">Change of time</label>
                </div>
                <div class="rejection-reason">
                    <input type="radio" id="reason_emergency" name="rejection_reason" value="Personal emergency">
                    <label for="reason_emergency">Personal emergency</label>
                </div>
                <div class="rejection-reason">
                    <input type="radio" id="reason_details" name="rejection_reason" value="Incomplete details">
                    <label for="reason_details">Incomplete details</label>
                </div>
                <div class="rejection-reason">
                    <input type="radio" id="reason_other" name="rejection_reason" value="Other">
                    <label for="reason_other">Other</label>
                </div>
                <input type="text" id="other_reason_text" name="other_reason_text" class="input-text" placeholder="Please specify" style="display: none; margin-top: 5px; width: 100%;">
                <div style="margin-top: 20px;">
                    <label for="rejection_description" class="form-label">Optional Description:</label>
                    <textarea name="rejection_description" id="rejection_description" class="input-text" rows="3" placeholder="Provide more details (optional)"></textarea>
                </div>
                <div style="text-align: right; margin-top: 25px;">
                    <button type="button" id="cancelRejectionBtn" class="btn-primary-soft btn">Cancel</button>
                    <button type="submit" name="confirm_rejection" class="login-btn btn-primary btn">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Get all modals for new requests
        var rejectionModal = document.getElementById("rejectionModal");
        var viewDetailsModal = document.getElementById("viewDetailsModal");

        // Get buttons that open these modals
        var rejectBtns = document.getElementsByClassName("reject-btn");
        var viewDetailsBtns = document.getElementsByClassName("btn-view");
        var closeBtns = document.querySelectorAll(".modal .close-btn");

        // Handler for View Details buttons
        for (let i = 0; i < viewDetailsBtns.length; i++) {
            viewDetailsBtns[i].onclick = function (event) {
                event.preventDefault();
                const button = this;
                document.getElementById('detailClientName').innerText = button.getAttribute('data-clientname');
                document.getElementById('detailSessionTitle').innerText = button.getAttribute('data-title');
                document.getElementById('detailDateTime').innerText = button.getAttribute('data-date') + ' at ' + button.getAttribute('data-time');
                document.getElementById('detailDescription').innerText = button.getAttribute('data-description');
                viewDetailsModal.style.display = "flex";
            }
        }

        // Handler for Reject buttons
        for (let i = 0; i < rejectBtns.length; i++) {
            rejectBtns[i].onclick = function (event) {
                event.preventDefault();
                const appoId = this.getAttribute('data-appoid');
                document.getElementById('rejectAppoId').value = appoId;
                rejectionModal.style.display = "flex";
            }
        }

        // Handler for all close buttons in modals
        for (let i = 0; i < closeBtns.length; i++) {
            closeBtns[i].onclick = function () {
                this.closest('.modal').style.display = "none";
            }
        }

        // Handler for cancel buttons
        if(document.getElementById('cancelRejectionBtn')) {
            document.getElementById('cancelRejectionBtn').onclick = function () {
                rejectionModal.style.display = "none";
            }
        }
        
        // Close modal if clicked outside
        window.onclick = function (event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = "none";
            }
        }

        // Rejection form logic
        const rejectionForm = document.getElementById('rejectionForm');
        if (rejectionForm) {
            const reasonRadios = rejectionForm.querySelectorAll('input[name="rejection_reason"]');
            const otherReasonText = document.getElementById('other_reason_text');
            const rejectionError = document.getElementById('rejectionError');

            reasonRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    if (this.id === 'reason_other' && this.checked) {
                        otherReasonText.style.display = 'block';
                        otherReasonText.setAttribute('required', 'required');
                    } else {
                        otherReasonText.style.display = 'none';
                        otherReasonText.removeAttribute('required');
                    }
                });
            });

            rejectionForm.addEventListener('submit', function (event) {
                const otherRadio = document.getElementById('reason_other');
                const selectedReason = rejectionForm.querySelector('input[name="rejection_reason"]:checked');
                rejectionError.style.display = 'none';

                if (!selectedReason) {
                    rejectionError.textContent = 'Please select a reason for rejection.';
                    rejectionError.style.display = 'block';
                    event.preventDefault();
                    return;
                }

                if (otherRadio.checked && otherReasonText.value.trim() === '') {
                    rejectionError.textContent = 'Please specify the reason for selecting "Other".';
                    rejectionError.style.display = 'block';
                    event.preventDefault();
                }
            });

            // Observer to reset form when modal is hidden
            const observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.attributeName === "style" && rejectionModal.style.display === 'none') {
                        rejectionForm.reset();
                        otherReasonText.style.display = 'none';
                        otherReasonText.removeAttribute('required');
                        rejectionError.style.display = 'none';
                    }
                });
            });
            observer.observe(rejectionModal, { attributes: true });
        }
    </script>
</body>

</html>