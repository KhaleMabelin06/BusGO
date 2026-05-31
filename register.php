<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BusGo - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5 d-flex justify-content-center">
    <div class="card shadow-sm p-4" style="width: 100%; max-width: 520px;">
        <p class="display-5 mb-3 text-center fw-bold">BusGo</p>
        <p class="text-center text-muted mb-4">Create your passenger account</p>

        <form method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" required>
                <div class="invalid-feedback">Full name is required.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
                <div class="invalid-feedback">Please enter a valid email.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_information" class="form-control" required>
                <div class="invalid-feedback">Contact number is required.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
                <div class="invalid-feedback">Username is required.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required minlength="6">
                <div class="invalid-feedback">Password must be at least 6 characters.</div>
            </div>
            <button type="submit" name="btnregister" class="btn btn-primary w-100">Register</button>
        </form>

        <hr>
        <p class="text-center mb-0 small">Already have an account? <a href="index.php">Login here</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (() => {
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>
</body>
</html>
<?php
    require_once "databaseconnection.php";
    require_once "verifyotpemail.php";

    //button function
    if(isset($_POST['btnregister'])) {
        //user inputs
        $full_name           = $_POST['full_name'];
        $email               = $_POST['email'];
        $contact_information = $_POST['contact_information'];
        $username            = $_POST['username'];
        $password            = md5($_POST['password']);
        $role                = 'customer';
        $otp                 = rand(100000, 999999);

        //check if username or email already exists
        $checksql = "SELECT user_id FROM ccm_tbl_users WHERE username = '$username' OR email = '$email'";
        $checkresult = $conn->query($checksql);

        if($checkresult->num_rows > 0) {
            echo "<script>
                Swal.fire({
                    icon: 'warning',
                    title: 'Already Exists',
                    text: 'Username or email is already taken. Please try another.',
                });
            </script>";
        } else {
            //string query
            $registersql = "INSERT INTO ccm_tbl_users (full_name, role, username, password, email, contact_information, otp, status)
                            VALUES ('$full_name', '$role', '$username', '$password', '$email', '$contact_information', '$otp', 'pending')";

            //converts the string to an actual query and transfers data to mysql
            $result = $conn->query($registersql);

            if($result == true) {
                send_verification($full_name, $email, $otp);

                //log action
                $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('" . $conn->insert_id . "', 'Registered', NOW())";
                $conn->query($logsql);

                echo "<script>
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Account Created!',
                        text: 'Please check your email for the verification code.',
                        showConfirmButton: true,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'otpverification.php';
                    });
                </script>";
            } else {
                echo $conn->error;
            }
        }
    }
?>