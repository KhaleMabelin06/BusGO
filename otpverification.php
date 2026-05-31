<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BusGo - OTP Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5 w-25 border border-primary rounded p-5">
        <div class="row mb-5">
            <div class="col text-center fw-bold">
                <span class="display-4 text-primary">OTP Verification</span>
            </div>
        </div>
        <div class="row my-3">
            <div class="col text-center fw-bold">
                <span class="text-primary h6">A one-time password (OTP) was sent to your email</span>
            </div>
        </div>
        <form action="otpverification.php" method="post">
            <div class="form-outline mb-4">
                <label class="form-label">Enter the OTP to verify your account</label>
                <input type="text" name="otp" class="form-control" required>
            </div>
            <input type="submit" name="btnverify" value="Verify" class="btn btn-primary btn-block w-100 mb-4">
        </form>
        <p class="text-center small">Already verified? <a href="index.php">Login here</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
<?php
    require_once "databaseconnection.php";
    if(isset($_POST['btnverify'])) {
        //users input
        $userotp = $_POST['otp'];

        //query
        $otpsql = "SELECT * FROM ccm_tbl_users WHERE otp = '$userotp'";
        $result = $conn->query($otpsql);

        if($result->num_rows == 1) {
            //updates the users status to active and clears the otp field in our database.
            $updatesql = "UPDATE ccm_tbl_users SET otp = NULL, status = 'active' WHERE otp = '$userotp'";
            $conn->query($updatesql);
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Account Verified!',
                    text: 'Your account is now active.',
                    showConfirmButton: false,
                    timer: 3000
                }).then(() => {
                    window.location.href = 'index.php';
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid OTP',
                    text: 'Please enter the correct OTP sent to your email.',
                });
            </script>";
        }
    }
?>