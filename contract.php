<?php
include 'connect.php';

// Helper to calculate contract end date
function getContractEndDate($type, $start_date) {
    $date = new DateTime($start_date);
    switch ($type) {
        case 'Part-Time':  $date->modify('+12 months'); return $date->format('Y-m-d');
        case 'Intern':     $date->modify('+6 months');  return $date->format('Y-m-d');
        case 'Contractor': $date->modify('+3 months');  return $date->format('Y-m-d');
        case 'Full-Time':  return 'Indefinite';
        default:           return 'Unknown';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resignation & Contracts</title>
    <style>
        /* Reset and layout */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            font-family: Arial, sans-serif;
        }

        /* Absolute background image */
        .background {
            background-image: url("bghome.png");
            background-size: cover;
            background-position: center;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        /* Container for content */
        .container {
            position: relative;
            z-index: 1;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo {
            width: 180px;
            margin-top: 30px;
        }

        nav {
            width: 80%;
            height: 70px;
            background: #fff;
            border-radius: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            box-shadow: 0 15px 20px -10px rgba(0, 0, 0, 0.2);
        }

        nav ul {
            list-style: none;
            display: flex;
            justify-content: space-around;
            width: 100%;
            padding: 0;
        }

        nav ul li {
            padding: 13px 35px;
            font-size: 18px;
            font-weight: 500;
            color: #777;
            cursor: pointer;
            position: relative;
            z-index: 2;
            transition: color 0.5s;
        }

        nav ul li::after {
            content: '';
            background: #4557f4;
            width: 100%;
            height: 100%;
            border-radius: 30px;
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
            opacity: 0;
            transition: top 0.5s, opacity 0.5s;
        }

        nav ul li:hover {
            color: #fff;
        }

        nav ul li:hover::after {
            top: 50%;
            opacity: 1;
        }

        /* Content section */
        .content {
            position: relative;
            z-index: 1;
            padding: 40px;
            width: 90%;
            max-width: 1200px;
            margin: auto;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            margin-top: 30px;
        }

        h3 {
            margin-bottom: 20px;
            font-family: Arial, sans-serif;
        }

        table.resign-table,
        table.contract-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f9f9f9;
        }

        .action-btn {
            padding: 6px 12px;
            margin: 2px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }

        .approve-btn {
            background-color: #28a745;
            color: white;
        }

        .approve-btn:hover {
            background-color: #218838;
        }

        .reject-btn {
            background-color: #dc3545;
            color: white;
        }

        .reject-btn:hover {
            background-color: #c82333;
        }

        .resigned {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="background"></div>

    <div class="content">
        <h3>Resignation Requests</h3>
        <?php
        $res = $conn->query("
            SELECT r.id, e.first_name, e.last_name, r.reason, r.status
            FROM resign_requests r
            JOIN nexaemp e ON r.employee_id = e.id
            ORDER BY r.id DESC
        ");

        if ($res->num_rows === 0) {
            echo "<p>No resignation requests.</p>";
        } else {
            echo "<table class='resign-table'>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>";
            while ($row = $res->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
                echo "<td>" . nl2br(htmlspecialchars($row['reason'])) . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                echo "<td>";
                if ($row['status'] === 'Pending') {
                    echo "<form method='post' action='process-resign.php' style='display:inline-block; margin-right:5px;'>
                            <input type='hidden' name='id' value='" . $row['id'] . "'>
                            <button type='submit' name='action' value='approve' class='action-btn approve-btn'>Approve</button>
                        </form>";
                    echo "<form method='post' action='process-resign.php' style='display:inline-block;'>
                            <input type='hidden' name='id' value='" . $row['id'] . "'>
                            <button type='submit' name='action' value='reject' class='action-btn reject-btn'>Reject</button>
                        </form>";
                } else {
                    echo htmlspecialchars($row['status']);
                }
                echo "</td></tr>";
            }
            echo "</tbody></table>";
        }
        ?>

        <h3>Employee Contract Details</h3>
        <?php
        $contract_query = $conn->query("
            SELECT 
                e.id, 
                e.first_name, 
                e.last_name, 
                j.job_title, 
                e.employee_type_enum, 
                e.created_at,
                COALESCE((
                    SELECT status 
                    FROM resign_requests r 
                    WHERE r.employee_id = e.id 
                    ORDER BY r.id DESC 
                    LIMIT 1
                ), 'None') AS resignation_status
            FROM nexaemp e
            LEFT JOIN jobs j ON e.job_id = j.id
            ORDER BY e.created_at DESC
        ");

        if ($contract_query->num_rows === 0) {
            echo "<p>No employee contract records found.</p>";
        } else {
            echo "<table class='contract-table'>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Job Title</th>
                        <th>Employee Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Resignation Status</th>
                    </tr>
                </thead>
                <tbody>";
            while ($row = $contract_query->fetch_assoc()) {
                $start_date = $row['created_at'];
                $end_date = getContractEndDate($row['employee_type_enum'], $start_date);
                $resign_status = $row['resignation_status'];

                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['job_title'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($row['employee_type_enum'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars(date('Y-m-d', strtotime($start_date))) . "</td>";
                echo "<td>" . htmlspecialchars($end_date) . "</td>";
                echo "<td>" . ($resign_status === 'Approved' ? "<span class='resigned'>Resigned</span>" : htmlspecialchars($resign_status)) . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
        ?>
    </div>
</body>
</html>
