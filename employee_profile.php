<?php
include 'connect.php';

if (!isset($_GET['id'])) {
    echo "Employee ID not provided.";
    exit;
}

$id = intval($_GET['id']);

// Fetch employee details and current manager info
$sql = "
    SELECT e.*, j.job_title, d.department_name AS department_name,
    m.manager_id,
    mgr.first_name AS mgr_first_name, mgr.last_name AS mgr_last_name
    FROM nexaemp e
    LEFT JOIN jobs j ON e.job_id = j.id
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN managers m ON e.id = m.employee_id
    LEFT JOIN nexaemp mgr ON m.manager_id = mgr.id
    WHERE e.id = $id
";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    echo "Employee not found.";
    exit;
}

$employee = mysqli_fetch_assoc($result);

// Fetch list of possible managers (all employees except this employee)
$managersSql = "SELECT id, first_name, last_name FROM nexaemp WHERE id != $id";
$managersResult = mysqli_query($conn, $managersSql);
$managersList = [];
while ($row = mysqli_fetch_assoc($managersResult)) {
    $managersList[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Employee Profile</title>
    <link rel="stylesheet" href="employee_profile.css">
</head>
<body>
    <div class="container">
        <img src="1_nexatech.png" class="logo" alt="Logo">
        <nav>
            <ul>
                <li><a href="homeMenu.html">Home</a></li>
                <li>Help</li>
                <li>About Us</li>
                <li>Profile</li>
                <li>Exit</li>
            </ul>
        </nav>
    </div>

    <div class="profile-container">
        <h1>Employee Profile</h1>
        <p><strong>Full Name:</strong> <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($employee['email']); ?></p>
        <p><strong>Birthday:</strong> <?= htmlspecialchars($employee['birthday']); ?></p>
        <p><strong>Employee Type:</strong> <?= htmlspecialchars($employee['employee_type_enum']); ?></p>
        <p><strong>Role:</strong> <?= htmlspecialchars($employee['role']); ?></p>
        <p><strong>Job Title:</strong> <?= htmlspecialchars($employee['job_title'] ?? 'Not assigned'); ?></p>
        <p><strong>Department:</strong> <?= htmlspecialchars($employee['department_name'] ?? 'Not assigned'); ?></p>
        <p><strong>Manager:</strong> 
            <?= $employee['manager_id'] ? 
                htmlspecialchars($employee['mgr_first_name'] . ' ' . $employee['mgr_last_name']) : 'None'; ?>
        </p>

        <a href="edit_employee.php?id=<?= $employee['id']; ?>">
            <button>Edit Info</button>
        </a>

        <h3>Assign Manager</h3>
        <form method="post" action="set_manager.php" onsubmit="return confirm('Are you sure you want to update the manager?');">
            <input type="hidden" name="employee_id" value="<?= $employee['id']; ?>">
            <select name="manager_id" required>
                <option value="">-- Select Manager --</option>
                <?php foreach ($managersList as $mgr): ?>
                    <option value="<?= $mgr['id']; ?>" 
                        <?= ($mgr['id'] == $employee['manager_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($mgr['first_name'] . ' ' . $mgr['last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Set Manager</button>
        </form>

        <div class="back-link">
            <a href="employee.php">← Back to Employee List</a>
        </div>
    </div>
</body>
</html>
