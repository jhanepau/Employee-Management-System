<?php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectId = $_POST['project_id'];
    $action = $_POST['action'];

    $status = ($action === 'approve') ? 'Approved' : 'Declined';

    $stmt = $conn->prepare("UPDATE projects SET status = ? WHERE project_id = ?");
    $stmt->bind_param("ss", $status, $projectId);

    if ($stmt->execute()) {
        echo "<script>alert('Project status updated to $status.'); window.location='projectadmin.php';</script>";
    } else {
        echo "<script>alert('Failed to update project status.'); history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
