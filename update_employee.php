<?php
include 'connect.php';

$id = intval($_POST['id']);
$first = $_POST['first_name'];
$last = $_POST['last_name'];
$email = $_POST['email'];
$bday = $_POST['birthday'];
$type = $_POST['employee_type'];

// job_id can be null or int
$job_id = (isset($_POST['job_id']) && $_POST['job_id'] !== '' && $_POST['job_id'] != 0) ? intval($_POST['job_id']) : null;

$department_id = intval($_POST['department_id']);

if ($job_id === null) {
    $sql = "UPDATE nexaemp SET 
        first_name = ?, 
        last_name = ?, 
        email = ?, 
        birthday = ?, 
        employee_type_enum = ?, 
        job_id = NULL, 
        department_id = ? 
        WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sssssii", $first, $last, $email, $bday, $type, $department_id, $id);
} else {
    // Verify that job_id exists in jobs table
    $checkJob = $conn->prepare("SELECT id FROM jobs WHERE id = ?");
    $checkJob->bind_param("i", $job_id);
    $checkJob->execute();
    $checkJob->store_result();
    if ($checkJob->num_rows === 0) {
        die("Invalid job selected.");
    }
    $checkJob->close();

    $sql = "UPDATE nexaemp SET 
        first_name = ?, 
        last_name = ?, 
        email = ?, 
        birthday = ?, 
        employee_type_enum = ?, 
        job_id = ?, 
        department_id = ? 
        WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sssssiii", $first, $last, $email, $bday, $type, $job_id, $department_id, $id);
}

if ($stmt->execute()) {
    header("Location: employee_profile.php?id=" . $id);
    exit;
} else {
    echo "Update failed: " . $stmt->error;
}
?>
