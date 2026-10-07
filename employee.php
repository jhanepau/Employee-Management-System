<?php 
include 'connect.php'; 

$query = "
    SELECT 
        e.id, 
        e.first_name, 
        e.last_name, 
        e.profile_picture,
        j.job_title
    FROM nexaemp e
    LEFT JOIN jobs j ON e.job_id = j.id
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Employee List</title>
    <link rel="stylesheet" href="employee.css">
    <style>
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="1_nexatech.png" class="logo">
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

    <div class="employee-container">
        <h3 id="empCount"><?php echo mysqli_num_rows($result); ?> Employees</h3>
        <hr>
        <label for="attsearch">Employee ID:</label>
        <input id="attsearch" type="search" placeholder="Search by ID">
    </div>

    <div class="employee_1" id="employeeList">
        <?php 
        mysqli_data_seek($result, 0);
        while ($row = mysqli_fetch_assoc($result)) { 
            $image_path = 'uploads/' . ($row['profile_picture'] ?? 'unknown.jpg');
            if (!file_exists($image_path) || empty($row['profile_picture'])) {
                $image_path = 'uploads/unknown.jpg';
            }
        ?>
            <div class="card employee-item" data-id="<?= $row['id']; ?>">
                <div class="card-border-top"></div>
                <div class="img">
                </div>
                <span><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></span>
                <p class="job"><?= htmlspecialchars($row['job_title'] ?? 'No Title'); ?></p>
                <form action="employee_profile.php" method="get">
                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                    <button type="submit">Click</button>
                </form>
            </div>
        <?php } ?>
    </div>

    <script>
        const searchInput = document.getElementById('attsearch');
        const empCount = document.getElementById('empCount');
        const cards = document.querySelectorAll('.employee-item');

        function updateVisibleCards(query) {
            let visibleCount = 0;
            cards.forEach(card => {
                const id = card.dataset.id.toLowerCase();
                if (id.includes(query)) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });
            empCount.textContent = `${visibleCount} Employee${visibleCount !== 1 ? 's' : ''}`;
        }

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLowerCase();
            updateVisibleCards(query);
        });

        cards.forEach(card => {
            card.addEventListener('click', () => {
                cards.forEach(c => c.classList.add('hidden'));
                card.classList.remove('hidden');
                empCount.textContent = `1 Employee`;
            });
        });
    </script>
</body>
</html>
