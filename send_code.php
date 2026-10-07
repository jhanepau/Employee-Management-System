<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

session_start();

$nexatech_email = $_POST['nexatech_email'];
$personal_email = $_POST['personal_email'];
$reset_code = rand(100000, 999999);

$_SESSION['reset_code'] = $reset_code;
$_SESSION['nexatech_email'] = $nexatech_email;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'jadebeannesevillad@gmail.com';
    $mail->Password = 'zvxagocvxrgcdwsw'; // Use the app password here
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('yourgmail@gmail.com', 'WST App');
    $mail->addAddress($personal_email);
    $mail->Subject = 'Your Password Reset Code';
    $mail->Body = "Here is your reset code: $reset_code";

    $mail->send();
    echo "Reset code sent successfully to your personal email.<br>";
    echo '<a href="verify_code.html">Click here to enter the code</a>';

} catch (Exception $e) {
    echo "Email failed to send. Error: {$mail->ErrorInfo}";
}
?>