<?php
session_start();
include 'connect.php';

$employee_id = $_POST['employee_id'];
$job = $_POST['job'];
$time_in = $_POST['time_in'];
$time_out = $_POST['time_out'];
$date = $_POST['date'];

$sql = "INSERT INTO employee_attendance (employee_id, job, time_in, time_out, date)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $employee_id, $job, $time_in, $time_out, $date);

if ($stmt->execute()) {
    echo "<script>
        alert('Attendance submitted successfully.');
        window.location.href = 'homeMenuEMP.html';
    </script>";
    exit;
} else {
    echo "<script>
        alert('Error: " . addslashes($stmt->error) . "');
        window.history.back();
    </script>";
    exit;
}

$stmt->close();
$conn->close();
?>