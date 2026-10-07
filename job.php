<?php 
include "connect.php";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Use LEFT JOIN on jobs so employees without jobs still appear
$sql = "
SELECT 
    n.id AS employee_id, n.first_name, n.last_name,
    j.id AS position_id, j.job_title AS position, j.min_salary, j.max_salary,
    m.manager_id,
    mgr.first_name AS manager_first_name,
    mgr.last_name AS manager_last_name
FROM nexaemp n
LEFT JOIN jobs j ON n.job_id = j.id
LEFT JOIN managers m ON n.id = m.employee_id
LEFT JOIN nexaemp mgr ON m.manager_id = mgr.id
WHERE n.is_deleted = FALSE AND n.is_approved = TRUE
";

// Search by employee ID or full name (case-insensitive)
if (!empty($search)) {
    $safe_search = $conn->real_escape_string($search);
    $sql .= " AND (CAST(n.id AS CHAR) LIKE '%$safe_search%' OR CONCAT(n.first_name, ' ', n.last_name) LIKE '%$safe_search%')";
}

$sql .= " ORDER BY n.id ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>JOB LIST</title>
    <link rel="stylesheet" href="jobstyle.css">
    <style>
        .action-btn {
            padding: 4px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            font-family: Arial, sans-serif;
            color: white;
            text-decoration: none;
            margin-right: 5px;
        }
        .edit-btn {
            background-color: #28a745;
        }
        .edit-btn:hover {
            background-color: #218838;
        }
        .delete-btn {
            background-color: #dc3545;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        form {
            display: inline;
            margin: 0;
        }
        .search-container {
            margin-bottom: 15px;
        }
        input[type="search"] {
            padding: 6px;
            font-size: 14px;
        }
        button[type="submit"] {
            padding: 6px 10px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
            border: 1px solid #999;
            background-color: #eee;
            margin-left: 5px;
        }
        button[type="submit"]:hover {
            background-color: #ddd;
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

<div class="report">
    <h1>Jobs</h1>
    <hr style="width: 1220px; height: 1px; background-color: black; border: none;">

    <form method="GET" id="searchForm" class="search-container">
        <label for="attsearch">Employee ID or Name:</label>
        <input
            id="attsearch"
            type="search"
            name="search"
            placeholder="Search"
            value="<?= htmlspecialchars($search) ?>"
            autocomplete="off"
        >
        <button type="submit">Search</button>
    </form>

    <div class="tablee">
        <table>
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Job ID</th>
                    <th>Job</th>
                    <th>Min Salary</th>
                    <th>Max Salary</th>
                    <th>Manager</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row["employee_id"]) ?></td>
                        <td><?= htmlspecialchars($row["first_name"] . " " . $row["last_name"]) ?></td>
                        <td><?= htmlspecialchars($row["position_id"] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row["position"] ?? 'N/A') ?></td>
                        <td><?= isset($row["min_salary"]) ? htmlspecialchars(number_format($row["min_salary"], 2)) : 'N/A' ?></td>
                        <td><?= isset($row["max_salary"]) ? htmlspecialchars(number_format($row["max_salary"], 2)) : 'N/A' ?></td>
                        <td>
                            <?php
                                if (!empty($row["manager_id"])) {
                                    echo htmlspecialchars($row["manager_first_name"] . " " . $row["manager_last_name"]);
                                } else {
                                    echo "None";
                                }
                            ?>
                        </td>
                        <td>
                            <a href="edit.php?id=<?= urlencode($row['employee_id']) ?>" class="action-btn edit-btn">Edit</a>
                            <form action="delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this employee?');" style="display:inline;">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($row['employee_id']) ?>">
                                <button type="submit" class="action-btn delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align:center;">No job records found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Auto-submit form 500ms after user stops typing
const searchInput = document.getElementById('attsearch');
let debounceTimer;

searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        document.getElementById('searchForm').submit();
    }, 500);
});
</script>

</body>
</html>

<?php
$conn->close();
?>
