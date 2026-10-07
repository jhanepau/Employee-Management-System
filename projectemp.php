<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['email'])) {
    die("Access denied. Please log in.");
}

$email = $_SESSION['email'];

// Get employee ID
$stmt = $conn->prepare("SELECT id, department_id FROM nexaemp WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$employee_id = $user['id'];
$employee_dept_id = $user['department_id'];

// Check for latest project
$check_stmt = $conn->prepare("SELECT * FROM projects WHERE employee_id = ? ORDER BY id DESC LIMIT 1");
$check_stmt->bind_param("i", $employee_id);
$check_stmt->execute();
$project_result = $check_stmt->get_result();
$latest_project = $project_result->fetch_assoc();

$has_pending = false;
$notification = "";

if ($latest_project) {
    if ($latest_project['status'] === 'Pending') {
        $has_pending = true;
    } elseif ($latest_project['status'] === 'Approved') {
        $notification = "✅ Your last submitted project was approved.";
    } elseif ($latest_project['status'] === 'Declined') {
        $notification = "❌ Your last submitted project was rejected.";
    }
}

// Generate new project ID
function generateProjectID($conn) {
    $result = $conn->query("SELECT project_id FROM projects ORDER BY id DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $lastId = $result->fetch_assoc()['project_id'];
        $num = intval(substr($lastId, 3)) + 1;
    } else {
        $num = 1;
    }
    return "PRJ" . str_pad($num, 3, '0', STR_PAD_LEFT);
}

$projectID = $has_pending ? '' : generateProjectID($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employee Project Management</title>
  <link rel="stylesheet" href="projectemp.css">
</head>
<body>
  <div class="container">
    <img src="1_nexatech.png" alt="">
    <h1>Employee Project</h1>

    <?php if ($notification): ?>
      <p style="color: green; font-weight: bold;"><?= $notification ?></p>
    <?php endif; ?>

    <?php if ($has_pending): ?>
      <p style="color: red; font-weight: bold;">⚠️ You already have a project pending. You cannot submit another until it is reviewed.</p>
    <?php else: ?>
    <form method="POST" action="submit-project.php">
      <div class="form-group">
        <label>Project ID:</label>
        <input type="text" name="proj_id" value="<?= $projectID ?>" readonly>
      </div>

      <div class="form-group">
        <label>Project Name:</label>
        <input type="text" name="proj_name" required>
      </div>

      <div class="form-group">
        <label>Start Date:</label>
        <input type="date" name="start_date" required>
      </div>

      <div class="form-group">
        <label>End Date:</label>
        <input type="date" name="end_date" required>
      </div>

      <div class="form-group">
        <label>Status:</label>
        <input type="text" name="status" value="Pending" readonly>
      </div>

      <input type="hidden" name="department_id" value="<?= $employee_dept_id ?>">

      <button type="submit">Submit</button>
    </form>
    <?php endif; ?>
  </div>
</body>
</html>
