<?php
session_start();
require_once '../databaseconnection.php';
if (isset($_SESSION['id'])) {
    $uid = $_SESSION['id'];

    $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) 
               VALUES ('$uid', 'Logged Out', NOW())";
    $conn->query($logsql);
}
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logging Out...</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Logged Out',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            window.location.href = '../index.php';
        });
    } else {
        window.location.href = '../index.php';
    }
</script>
</body>
</html>