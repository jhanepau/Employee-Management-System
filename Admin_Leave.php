<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.html");
    exit;
}

$employee_id = $_SESSION['id'];
$job = "";

// Fetch job from database with join
$sql = "
    SELECT j.job_title 
    FROM nexaemp e
    JOIN jobs j ON e.job_id = j.id
    WHERE e.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$stmt->bind_result($job);
$stmt->fetch();
$stmt->close();

// Check leave status
$leave_status = '';
$can_submit = true;

$leave_check_sql = "SELECT status FROM employee_leave WHERE employee_id = ? ORDER BY start_date DESC LIMIT 1";
$check_stmt = $conn->prepare($leave_check_sql);
$check_stmt->bind_param("i", $employee_id);
$check_stmt->execute();
$check_stmt->bind_result($leave_status);
$check_stmt->fetch();
$check_stmt->close();

if ($leave_status === 'Pending') {
    $can_submit = false;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Employee Leave Record</title>
  <link rel="stylesheet" href="leaveemployee.css"/>
</head>

<body>
  <div class="container">
    <img src="1_nexatech.png" alt="Logo" />
    <h1>Employee Leave Record</h1>

    <?php if (!$can_submit): ?>
      <p style="color: red; font-weight: bold;">
        You already have a <strong>Pending</strong> leave request. Please wait for it to be approved or declined.
      </p>
    <?php elseif ($leave_status === 'Approved'): ?>
      <p style="color: green;">
        Your previous leave request was <strong>Approved</strong>.
      </p>
    <?php elseif ($leave_status === 'Declined'): ?>
      <p style="color: orange;">
        Your previous leave request was <strong>Declined</strong>.
      </p>
    <?php endif; ?>

    <form action="AdminLeave.php" method="POST">
      <div class="form-group">
        <label for="emp-id">Employee ID:</label>
        <input type="text" id="emp-id" name="employee_id" value="<?= $employee_id ?>" readonly />
      </div>
      
      <div class="form-group">
        <label for="job">Job:</label>
        <input type="text" id="job" name="job" value="<?= htmlspecialchars($job) ?>" readonly />
      </div>
      
      <div class="form-group">
        <label for="leave_type">Leave Type:</label>
        <select id="leave_type" name="leave_type" required <?= !$can_submit ? 'disabled' : '' ?>>
          <option value="">Select Leave Type</option>
          <option value="Vacation">Vacation</option>
          <option value="Sick">Sick</option>
          <option value="Personal Leave">Personal Leave</option>
        </select>
      </div>
      
      <div class="form-group">
        <label for="start-date">Start Date:</label>
        <input type="date" id="start-date" name="start_date" required <?= !$can_submit ? 'disabled' : '' ?> />
      </div>
      
      <div class="form-group">
        <label for="end-date">End Date:</label>
        <input type="date" id="end-date" name="end_date" required <?= !$can_submit ? 'disabled' : '' ?> />
      </div>
      
      <div class="form-group">
        <label for="status">Status:</label>
        <input type="text" id="status" name="status" value="Pending" readonly />
      </div>
      
      <button type="submit" <?= !$can_submit ? 'disabled' : '' ?>>Submit</button>
    </form>
  </div>
</body>
</html>
