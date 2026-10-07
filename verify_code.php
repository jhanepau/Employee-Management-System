<?php
session_start();

$input = $_POST['code_input'];

if ($input == $_SESSION['reset_code']) {
    echo "Code verified!<br>";
    echo '<a href="reset_password.html">Click to reset password</a>';
} else {
    echo "Invalid code.";
}
?>