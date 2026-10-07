<?php 
session_start();
include 'connect.php'; 

if (!isset($_SESSION['id'])) {
    header("Location: login.html");
    exit;
}

$Id = $_SESSION['id'];

$job = "";
$sql = "SELECT jobs.job_title FROM nexaemp 
        INNER JOIN jobs ON nexaemp.job_id = jobs.id 
        WHERE nexaemp.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $Id);
$stmt->execute();
$stmt->bind_result($job);
$stmt->fetch();
$stmt->close();
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Attendance</title>
    <link rel="stylesheet" href="Attendance.css">
</head>
<body>
    <div class="container">
        <img src="1_nexatech.png" alt="">
        <h1>Employee Attendance Record</h1>
        <form action="AdminAttendance.php" method="POST">
            <div class="form-group">
                <label for="empID">Employee ID:</label>
                <input type="number" id="empID" name="employee_id" value="<?php echo $Id; ?>" readonly>
            </div>

            <div class="form-group">
                <label for="job">Job:</label>
                <input type="text" name="job" value="<?php echo htmlspecialchars($job); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="timeIn">Time In:</label>
                <input type="time" id="timeIn" name="time_in" required>
            </div>

            <div class="form-group">
                <label for="timeOut">Time Out:</label>
                <input type="time" id="timeOut" name="time_out" required>
            </div>

            <div class="form-group">
                <label for="date">Date:</label>
                <input type="date" id="date" name="date" readonly>
            </div>

            <button type="submit">Submit Attendance</button>
        </form>
    </div>

    <script>
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('date').value = today;
    </script>
</body>
</html>