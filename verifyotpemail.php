<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function send_verification($fullname, $email, $otp) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'email@here.com';
        $mail->Password   = 'your password';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('email@here.com', 'BusGo Reservation System');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Verify Your BusGo Account";
        $mail->Body    = "Hello " . $fullname . ",<br><br>Thank you for registering with BusGo!<br>Your account verification code is: <b>" . $otp . "</b><br><br>Enter this code on the verification page to activate your account.";

        $mail->send();

        echo "
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Email Sent!',
                text: 'A verification code has been sent to your email.',
                confirmButtonText: 'OK'
            });
        </script>
        ";

    } catch (Exception $e) {
        echo "
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Email Failed!',
                text: 'Verification email could not be sent.',
                footer: '" . $mail->ErrorInfo . "'
            });
        </script>
        ";
    }
}
?>
