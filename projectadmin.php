<?php
include 'connect.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin - Project Management</title>
    <link rel="stylesheet" href="projectadmin.css">
    <style>
        .hidden { display: none; }
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
        <h1>Project Report</h1>
        <hr style="width: 100%; height: 1px; background-color: black; border: none;">
        <label for="attsearch">Project ID:</label>
        <input id="attsearch" type="search" placeholder="Search by Project ID">

        <div class="tablee">
            <table id="projectTable">
                <thead>
                    <tr>
                        <th>Project ID</th>
                        <th>Project Name</th>
                        <th>Employee ID</th>
                        <th>Department Name</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $sql = "SELECT p.*, d.department_name 
                        FROM projects p 
                        LEFT JOIN departments d ON p.department_id = d.id";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $deptName = $row['department_name'] ?? 'Not assigned';

                        echo "<tr class='project-row'>
                                <td>{$row['project_id']}</td>
                                <td>{$row['project_name']}</td>
                                <td>{$row['employee_id']}</td>
                                <td>{$deptName}</td>
                                <td>{$row['start_date']}</td>
                                <td>{$row['end_date']}</td>
                                <td>{$row['status']}</td>
                                <td>";
                        if ($row['status'] === 'Pending') {
                            echo "<form action='update-status.php' method='POST' style='display:inline;'>
                                    <input type='hidden' name='project_id' value='{$row['project_id']}'>
                                    <button type='submit' name='action' value='approve'>Approve</button>
                                    <button type='submit' name='action' value='decline'>Decline</button>
                                  </form>";
                        } else {
                            echo $row['status'];
                        }
                        echo "</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No projects found.</td></tr>";
                }

                $conn->close();
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('attsearch');
        const rows = document.querySelectorAll('.project-row');

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.toLowerCase().trim();
            rows.forEach(row => {
                const projectId = row.children[0].textContent.toLowerCase();
                row.classList.toggle('hidden', !projectId.includes(query));
            });
        });
    </script>
</body>
</html>
