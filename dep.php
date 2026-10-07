<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dep ADMIN</title>
    <link rel="stylesheet" href="depdesign.css" />
    <style>
        .hidden {
            display: none;
        }
    </style>
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

    <div id="cards">
        <div class="card">
            <div class="card-image1"></div>
            <div class="heading">Accounting Department</div>
            <button class="btn" onclick="window.location.href='depreport.php'">→</button>
        </div>

        <div class="card">
            <div class="card-image2"></div>
            <div class="heading">HR Department</div>
            <button class="btn" onclick="window.location.href='depreport1.php'">→</button>
        </div>

        <div class="card">
            <div class="card-image3"></div>
            <div class="heading">IT Department</div>
            <button class="btn" onclick="window.location.href='depreport2.php'">→</button>
        </div>
    </div>

    <div class="report">
        <h1>Departments</h1>
        <hr style="width: 1220px; height: 1px; background-color: black; border: none;" />
        <label for="attsearch">Department Name or ID:</label>
        <input id="attsearch" type="search" placeholder="Search..." />

        <div class="table1">
            <table id="departmentTable">
                <thead>
                    <tr>
                        <th>Department ID</th>
                        <th>Department Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include "connect.php";

                    // Just get departments, no manager join needed now
                    $sql = "SELECT id AS department_id, department_name FROM departments ORDER BY id";

                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['department_id']) ?></td>
                        <td class="dep-name"><?= htmlspecialchars($row['department_name']) ?></td>
                    </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="2" style="text-align:center;">No departments found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('attsearch');
        const tableRows = document.querySelectorAll("#departmentTable tbody tr");

        searchInput.addEventListener('input', () => {
            const keyword = searchInput.value.toLowerCase().trim();

            tableRows.forEach(row => {
                const depID = row.cells[0].textContent.toLowerCase();
                const depName = row.querySelector('.dep-name').textContent.toLowerCase();

                row.style.display = (depID.includes(keyword) || depName.includes(keyword)) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
