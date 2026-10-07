<?php
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_POST['employee_id'];
    $start_date = $_POST['start_date'];
    $action = $_POST['action'];

    $status = $action === 'Approve' ? 'Approved' : 'Declined';

    $sql = "UPDATE employee_leave SET status = ? WHERE employee_id = ? AND start_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sis", $status, $employee_id, $start_date);

    if ($stmt->execute()) {
        header("Location: {$_SERVER['HTTP_REFERER']}");
        exit;
    } else {
        echo "Error updating leave status: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
