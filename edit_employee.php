<?php
include 'connect.php';

if (!isset($_GET['id'])) {
    die("Missing employee ID.");
}

$id = intval($_GET['id']);

// Get employee data
$sql = "SELECT * FROM nexaemp WHERE id = $id";
$result = mysqli_query($conn, $sql);
if (!$result || mysqli_num_rows($result) === 0) {
    die("Employee not found.");
}
$emp = mysqli_fetch_assoc($result);

// Get list of jobs
$jobs_result = mysqli_query($conn, "SELECT id, job_title FROM jobs");
$jobs = [];
while ($row = mysqli_fetch_assoc($jobs_result)) {
    $jobs[] = $row;
}

// Get list of departments
$dept_result = mysqli_query($conn, "SELECT id, department_name FROM departments");
$departments = [];
while ($row = mysqli_fetch_assoc($dept_result)) {
    $departments[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Employee</title>
    <link rel="stylesheet" href="edit_employee.css">
</head>
<body>
    <div class="container">
        <img src="1_nexatech.png" class="logo" alt="Logo">
        <nav>
            <ul>
                <li>Home</li>
                <li>Help</li>
                <li>About Us</li>
                <li>Profile</li>
                <li>Exit</li>
            </ul>
        </nav>
    </div>

    <h1>Edit Employee Info</h1>
    <form action="update_employee.php" method="post">
        <input type="hidden" name="id" value="<?php echo $emp['id']; ?>">

        <label>First Name:</label>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($emp['first_name']); ?>" required><br>

        <label>Last Name:</label>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($emp['last_name']); ?>" required><br>

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($emp['email']); ?>" required><br>

        <label>Birthday:</label>
        <input type="date" name="birthday" value="<?php echo $emp['birthday']; ?>" required><br>

        <label>Employee Type:</label>
        <select name="employee_type" required>
            <?php
            $types = ['Full-Time', 'Part-Time', 'Intern', 'Contractor'];
            foreach ($types as $type) {
                $selected = $emp['employee_type_enum'] === $type ? "selected" : "";
                echo "<option value='$type' $selected>$type</option>";
            }
            ?>
        </select><br>

<label>Job Title:</label>
<select name="job_id">
    <option value="">-- Not Assigned --</option>
    <?php foreach ($jobs as $job): ?>
        <option value="<?= $job['id'] ?>" <?= ($job['id'] == $emp['job_id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($job['job_title']) ?>
        </option>
    <?php endforeach; ?>
</select>


        <label>Department:</label>
        <select name="department_id" required>
            <?php
            foreach ($departments as $dept) {
                $selected = $dept['id'] == $emp['department_id'] ? "selected" : "";
                echo "<option value='{$dept['id']}' $selected>{$dept['department_name']}</option>";
            }
            ?>
        </select><br>

        <button type="submit">Update</button>
    </form>
</body>
</html>
