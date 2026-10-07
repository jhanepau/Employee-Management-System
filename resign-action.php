<?php
session_start();
include 'connect.php';

if ($_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

$id = (int)$_POST['id'];
$action = $_POST['action'];

$status = $action === 'approve' ? 'Approved' : 'Rejected';

$stmt = $conn->prepare("UPDATE resign_requests SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);
$stmt->execute();

header("Location: resign-manage.php");
exit();
