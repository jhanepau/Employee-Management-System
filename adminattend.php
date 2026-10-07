<?php
$conn = new mysqli("localhost", "root", "", "nexatech");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "
SELECT 
    nexaemp.first_name, 
    nexaemp.last_name, 
    d.department_name AS department, 
    j.job_title AS job,
    employee_attendance.time_in, 
    employee_attendance.time_out, 
    employee_attendance.date
FROM employee_attendance
INNER JOIN nexaemp ON nexaemp.id = employee_attendance.employee_id
LEFT JOIN departments d ON nexaemp.department_id = d.id
LEFT JOIN jobs j ON nexaemp.job_id = j.id
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ADMIN ATTENDANCE REPORT</title>
    <link rel="stylesheet" href="adminattendance.css">
    <style>
        /* Optional: make filters inline */
        .filters {
            margin-bottom: 15px;
        }
        .filters > * {
            margin-right: 10px;
            padding: 5px;
        }
    </style>
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

<div class="reports">
    <h1>Attendance Report</h1>
    <hr style="width: 100%; height: 1px; background-color: black; border: none;">

    <div class="filters">
        <input id="attsearch" type="search" placeholder="Search by employee name">

        <label for="deptFilter">Filter by Department:</label>
        <select id="deptFilter">
            <option value="">All Departments</option>
            <option value="HR Department">HR Department</option>
            <option value="Accounting Department">Accounting Department</option>
            <option value="IT Department">IT Department</option>
        </select>

        <label for="dateFilter">Filter by Date:</label>
        <input type="date" id="dateFilter">
    </div>

    <div class="tablee">
        <table id="attendanceTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Job</th>
                    <th>Department</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="emp-name"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td class="emp-job"><?= htmlspecialchars($row['job']) ?></td>
                            <td class="emp-dept"><?= htmlspecialchars($row['department']) ?></td>
                            <td><?= htmlspecialchars(date("g:i A", strtotime($row['time_in']))) ?></td>
                            <td><?= htmlspecialchars(date("g:i A", strtotime($row['time_out']))) ?></td>
                            <td class="emp-date"><?= htmlspecialchars($row['date']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">No attendance records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    const searchInput = document.getElementById('attsearch');
    const deptFilter = document.getElementById('deptFilter');
    const dateFilter = document.getElementById('dateFilter');
    const tbody = document.getElementById('attendanceTable').tBodies[0];

    function filterTable() {
        const searchText = searchInput.value.toLowerCase().trim();
        const deptText = deptFilter.value.toLowerCase();
        const dateText = dateFilter.value; // YYYY-MM-DD format

        for (const row of tbody.rows) {
            const name = row.querySelector('.emp-name').textContent.toLowerCase();
            const dept = row.querySelector('.emp-dept').textContent.toLowerCase();
            const date = row.querySelector('.emp-date').textContent;

            const matchesName = name.includes(searchText);
            const matchesDept = deptText === '' || dept === deptText;
            const matchesDate = dateText === '' || date === dateText;

            row.style.display = (matchesName && matchesDept && matchesDate) ? '' : 'none';
        }
    }

    searchInput.addEventListener('input', filterTable);
    deptFilter.addEventListener('change', filterTable);
    dateFilter.addEventListener('change', filterTable);
</script>

</body>
</html>
