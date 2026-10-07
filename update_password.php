<?php
session_start();
include 'connect.php';

if (!isset($_POST['new_password']) || empty($_SESSION['nexatech_email'])) {
    die("Invalid request.");
}

$new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
$nexatech_email = $_SESSION['nexatech_email'];

// Fix table name from 'users' to 'nexaemp'
$sql = "UPDATE nexaemp SET password = ? WHERE email = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL prepare failed: " . $conn->error);
}

$stmt->bind_param("ss", $new_password, $nexatech_email);

if ($stmt->execute()) {
    echo "Password updated successfully.";
    session_destroy();
} else {
    echo "Failed to update password: " . $stmt->error;
}
?>