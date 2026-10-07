<?php
include "connect.php";

if (!isset($_GET['id'])) {
    header("Location: job.php");
    exit;
}

$id = (int)$_GET['id'];

// Fetch employee and current job info
$sql = "SELECT n.id, n.first_name, n.last_name, n.job_id, j.job_title
        FROM nexaemp n
        LEFT JOIN jobs j ON n.job_id = j.id
        WHERE n.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Employee not found.";
    exit;
}

$employee = $result->fetch_assoc();

// Get the department ID from the employee's current job
$dept_sql = "SELECT department_id FROM jobs WHERE id = ?";
$dept_stmt = $conn->prepare($dept_sql);
$dept_stmt->bind_param("i", $employee['job_id']);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

if ($dept_result->num_rows === 0) {
    echo "Department not found.";
    exit;
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];

// Fetch only jobs from the same department
$jobs_stmt = $conn->prepare("SELECT id, job_title FROM jobs WHERE department_id = ? ORDER BY job_title");
$jobs_stmt->bind_param("i", $department_id);
$jobs_stmt->execute();
$jobs_result = $jobs_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Employee Job</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f4f4f4;
        }

        h1 {
            color: #333;
        }

        form {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        a {
            display: inline-block;
            margin-left: 15px;
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <h1>Edit Job for <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></h1>

    <form method="POST" action="edit.php?id=<?= $employee['id'] ?>">
        <label for="job_id">Job Position:</label>
        <select name="job_id" id="job_id" required>
            <option value="">Select a job</option>
            <?php while ($job = $jobs_result->fetch_assoc()): ?>
                <option value="<?= $job['id'] ?>" <?= $job['id'] == $employee['job_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($job['job_title']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br><br>
        <button type="submit">Save</button>
        <a href="job.php">Cancel</a>
    </form>

<?php
// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_job_id = (int)$_POST['job_id'];
    if ($new_job_id) {
        $update_sql = "UPDATE nexaemp SET job_id = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $new_job_id, $id);
        if ($update_stmt->execute()) {
            header("Location: job.php?message=Updated");
            exit;
        } else {
            echo "Error updating job: " . $conn->error;
        }
    } else {
        echo "Please select a valid job.";
    }
}
?>

</body>
</html>
