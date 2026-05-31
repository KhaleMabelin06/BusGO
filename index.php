<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BusGo - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5 w-25 border border-primary rounded p-5">
        <div class="row mb-4">
            <div class="col text-center fw-bold">
                <span class="display-2 text-primary">BusGo</span>
            </div>
        </div>
        <form action="index.php" method="post">
            <div class="form-outline mb-4">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="form-outline mb-4">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <input type="submit" name="btnlogin" value="Log In" class="btn btn-primary btn-block w-100 mb-4">
        </form>
        <p class="text-center small">No account yet? <a href="register.php">Register here</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
<?php
    require_once "databaseconnection.php";
    session_start();

    if(isset($_POST['btnlogin'])) {
        //parse user inputs
        $username = $_POST['username'];
        $password = md5($_POST['password']);

        //prevents pending accounts from logging in. basically requires otp verification step first.
        $loginsql = "SELECT * FROM ccm_tbl_users WHERE username = '$username' AND password = '$password' AND status = 'active'";
        $result = $conn->query($loginsql);
        if($result->num_rows == 1) {
            $fieldnames = $result->fetch_assoc();

            $role     = $fieldnames['role'];
            $fullname = $fieldnames['full_name'];
            $id       = $fieldnames['user_id'];

            $_SESSION['role']     = $role;
            $_SESSION['fullname'] = $fullname;
            $_SESSION['id']       = $id;
            //logs entry
            $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('" . $_SESSION['id'] . "', 'Logged In', NOW())";
            $conn->query($logsql);

            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Login Successful',
                    text: 'Welcome back, $fullname!',
                }).then(() => {
                    if ('$role' === 'admin') {
                        window.location.href = 'admin/dashboard.php';
                    } else if ('$role' === 'employee') {
                        window.location.href = 'employee/dashboard.php';
                    } else if ('$role' === 'customer') {
                        window.location.href = 'customer/dashboard.php';
                    }
                });
            </script>";
        } else {
            //check if account exists but is still pending. if pending, it prompts the user to verify their account first.
            $checksql = "SELECT * FROM ccm_tbl_users WHERE username = '$username' AND password = '$password' AND status = 'pending'";
            $checkresult = $conn->query($checksql);

            if($checkresult->num_rows == 1) {
                echo "<script>
                    Swal.fire({
                        icon: 'warning',
                        title: 'Account Not Verified',
                        text: 'Please verify your email first before logging in.',
                    }).then(() => {
                        window.location.href = 'otpverification.php';
                    });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: 'Invalid username or password. Please try again.',
                    });
                </script>";
            }
        }
    }
?>