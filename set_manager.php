<?php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = intval($_POST['employee_id']);
    $manager_id = intval($_POST['manager_id']);

    if ($employee_id === $manager_id) {
        echo "<script>alert('Employee cannot be their own manager.'); window.history.back();</script>";
        exit;
    }

    // Check if the employee already has a manager record
    $checkSql = "SELECT * FROM managers WHERE employee_id = $employee_id";
    $checkResult = mysqli_query($conn, $checkSql);

    if (mysqli_num_rows($checkResult) > 0) {
        // Update existing
        $updateSql = "UPDATE managers SET manager_id = $manager_id WHERE employee_id = $employee_id";
        $success = mysqli_query($conn, $updateSql);
    } else {
        // Insert new
        $insertSql = "INSERT INTO managers (manager_id, employee_id) VALUES ($manager_id, $employee_id)";
        $success = mysqli_query($conn, $insertSql);
    }

    if ($success) {
        echo "<script>alert('Manager assigned successfully.'); window.location.href = 'employee_profile.php?id=$employee_id';</script>";
    } else {
        echo "<script>alert('Failed to assign manager.'); window.history.back();</script>";
    }
} else {
    echo "Invalid request.";
}
?>
