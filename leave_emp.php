<?php
session_start();
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $employee_id = $_POST['employee_id'] ?? '';
    $job = $_POST['job'] ?? '';
    $leave_type = $_POST['leave_type'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $status = $_POST['status'] ?? '';

    // Validation
    if (empty($employee_id) || empty($leave_type) || empty($start_date) || empty($end_date)) {
        die("Please fill all required fields.");
    }

    // Insert into the database
    $sql = "INSERT INTO employee_leave (employee_id, job, leave_type, start_date, end_date, status)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $employee_id, $job, $leave_type, $start_date, $end_date, $status);

    if ($stmt->execute()) {
        echo "Leave record successfully added.";
         sleep(2); // Wait for 2 seconds
        header("Location: homeMenuEMP.html");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>