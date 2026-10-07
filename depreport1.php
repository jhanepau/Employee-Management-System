<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>HR Department Report</title>
    <link rel="stylesheet" href="depreport.css" />
</head>
<body>
    <div class="container">
        <img src="1_nexatech.png" class="logo" />
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

    <div class="report">
        <h1>HR Department</h1>
        <hr style="width: 1220px; height: 1px; background-color: black; border: none;" />

        <label for="attsearch">Search by Employee ID:</label>
        <input id="attsearch" type="search" placeholder="Enter Employee ID" />

        <div class="table1">
            <table id="empTable">
                <thead>
                    <tr>
                        <th>Department ID</th>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Manager ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include "connect.php";

                    // Get HR department ID
                    $deptSql = "SELECT id FROM departments WHERE department_name = 'HR Department'";
                    $deptResult = $conn->query($deptSql);

                    if ($deptResult && $deptResult->num_rows > 0) {
                        $deptRow = $deptResult->fetch_assoc();
                        $deptId = $deptRow['id'];

                        // Fetch employees and their manager IDs from managers table
                        $empSql = "
                            SELECT 
                                e.id AS employee_id,
                                e.first_name,
                                e.last_name,
                                m.manager_id
                            FROM nexaemp e
                            LEFT JOIN managers m ON e.id = m.employee_id
                            WHERE e.department_id = $deptId
                              AND e.is_deleted = FALSE
                              AND e.is_approved = TRUE
                        ";

                        $empResult = $conn->query($empSql);

                        if ($empResult && $empResult->num_rows > 0) {
                            while ($emp = $empResult->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($deptId) . "</td>";
                                echo "<td class='emp-id'>" . htmlspecialchars($emp['employee_id']) . "</td>";
                                echo "<td>" . htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) . "</td>";
                                echo "<td>" . ($emp['manager_id'] ? htmlspecialchars($emp['manager_id']) : 'None') . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo '<tr><td colspan="4" style="text-align:center;">No employees found.</td></tr>';
                        }
                    } else {
                        echo '<tr><td colspan="4" style="text-align:center;">HR department not found.</td></tr>';
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('attsearch');
        const rows = document.querySelectorAll('#empTable tbody tr');

        searchInput.addEventListener('input', () => {
            const filter = searchInput.value.trim().toLowerCase();

            rows.forEach(row => {
                const empID = row.querySelector('.emp-id').textContent.toLowerCase();
                row.style.display = empID.includes(filter) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
