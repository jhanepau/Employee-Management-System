<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['email'])) {
    die("Access denied.");
}

$email = $_SESSION['email'];

// Get employee ID
$stmt = $conn->prepare("SELECT id FROM nexaemp WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$employee_id = $user['id'];

$project_id = $_POST['proj_id'];
$project_name = $_POST['proj_name'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$status = $_POST['status'];
$department_id = $_POST['department_id'];

// Insert into projects table
$stmt = $conn->prepare("INSERT INTO projects (project_id, project_name, employee_id, department_id, start_date, end_date, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssiisss", $project_id, $project_name, $employee_id, $department_id, $start_date, $end_date, $status);

if ($stmt->execute()) {
    header("Location: projectemp.php");
    exit;
} else {
    echo "Error: " . $stmt->error;
}
?>
