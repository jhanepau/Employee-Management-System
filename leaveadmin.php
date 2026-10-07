<?php
// Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "nexatech";
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT 
            el.employee_id, 
            ne.first_name, 
            ne.last_name, 
            el.job, 
            el.leave_type, 
            el.start_date, 
            el.end_date, 
            el.status 
        FROM employee_leave el
        JOIN nexaemp ne ON el.employee_id = ne.id
        ORDER BY el.start_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Leave Report</title>
    <link rel="stylesheet" href="leave.css">
    <style>
        .filters {
            margin-bottom: 15px;
        }
        .filters input {
            padding: 5px;
            margin-right: 10px;
        }
    </style>
</head>
<body>

<!-- Background container with navbar and logo -->
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

<div class="reports">
    <h1>Employee Leave Report</h1>
    <hr style="width: 1220px; height: 1px; background-color: black; border: none;">

    <div class="filters">
        <label for="attsearch">Search by Name:</label>
        <input id="attsearch" type="search" placeholder="Search employee name">
    </div>

    <div class="tablee">
        <table id="leaveTable">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Full Name</th>
                    <th>Job</th>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Action</th> 
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['employee_id']) ?></td>
                            <td class="emp-name"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['job']) ?></td>
                            <td><?= htmlspecialchars($row['leave_type']) ?></td>
                            <td><?= htmlspecialchars($row['start_date']) ?></td>
                            <td><?= htmlspecialchars($row['end_date']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td>
                                <?php if ($row['status'] == 'Pending'): ?>
                                    <form action="handle_leave_action.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="employee_id" value="<?= $row['employee_id'] ?>">
                                        <input type="hidden" name="start_date" value="<?= $row['start_date'] ?>">
                                        <button type="submit" name="action" value="Approve">Approve</button>
                                        <button type="submit" name="action" value="Decline">Decline</button>
                                    </form>
                                <?php else: ?>
                                    <?= htmlspecialchars($row['status']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">No leave records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    const searchInput = document.getElementById('attsearch');
    const table = document.getElementById('leaveTable').tBodies[0];

    function filterTable() {
        const searchText = searchInput.value.toLowerCase().trim();

        for (const row of table.rows) {
            const name = row.querySelector('.emp-name').textContent.toLowerCase();
            const match = name.includes(searchText);
            row.style.display = match ? '' : 'none';
        }
    }

    searchInput.addEventListener('input', filterTable);
</script>

</body>
</html>

<?php $conn->close(); ?>
