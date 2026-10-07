<?php
session_start();
include 'connect.php';

// Only the master admin can perform approvals
if (!isset($_SESSION['email']) || $_SESSION['email'] !== 'admin@admin.nexatech.com') {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $id = (int)$_POST['id'];

    $stmt = $conn->prepare("UPDATE nexaemp SET is_approved = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Account approved successfully.');
                window.location.href = 'admin-approve.php';
              </script>";
    } else {
        echo "<script>
                alert('Error approving account: " . addslashes($stmt->error) . "');
                window.location.href = 'admin-approve.php';
              </script>";
    }

    $stmt->close();
} else {
    header("Location: admin-approve.php");
}

$conn->close();
?>
