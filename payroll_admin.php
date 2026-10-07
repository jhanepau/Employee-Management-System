<?php
include "connect.php";

// MAIN payroll SQL query
$sql = "
SELECT 
    n.id AS employee_id,
    n.first_name,
    n.last_name,
    n.role,
    n.employee_type_id,
    j.job_title AS position,
    d.department_name,
    mgr.manager_id,
    j.min_salary,
    j.max_salary
FROM nexaemp n
LEFT JOIN jobs j ON n.job_id = j.id
LEFT JOIN departments d ON j.department_id = d.id
LEFT JOIN managers mgr ON n.id = mgr.employee_id
WHERE n.is_deleted = FALSE 
  AND n.is_approved = TRUE
ORDER BY n.id ASC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>PAYROLL</title>
    <link rel="stylesheet" href="p_adminstyle.css">
    <style>
        .filters {
            margin-bottom: 20px;
        }
        .filters input {
            padding: 5px;
            width: 300px;
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
    <h1>Payroll Report</h1>
    <hr style="width: 100%; height: 1px; background-color: black; border: none;">
    
    <div class="filters">
        <label for="attsearch">Search by Employee ID:</label>
        <input id="attsearch" type="search" placeholder="Enter Employee ID" autocomplete="off">
    </div>

    <div class="tablee">
        <table id="payrollTable">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Full Name</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Manager ID</th>
                    <th>Salary</th>
                    <th>Deduction</th>
                    <th>Bonus</th>
                    <th>Net Salary</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $employee_id = $row['employee_id'];
                    $type_id = (int)$row['employee_type_id'];

                    $min_salary = $row['min_salary'];
                    $max_salary = $row['max_salary'];
                    $salary = is_null($min_salary) || is_null($max_salary) ? 0 : ($min_salary + $max_salary) / 2;

                    // === Get bonus percentage ===
                    $bonus_percentage = 0;
                    $ruleSql = "
                        SELECT bonus_percentage 
                        FROM payroll_rules
                        WHERE employee_type_id = $type_id 
                          AND min_salary <= $salary 
                          AND max_salary >= $salary
                        LIMIT 1
                    ";
                    $ruleResult = $conn->query($ruleSql);
                    if ($ruleResult && $ruleResult->num_rows > 0) {
                        $rule = $ruleResult->fetch_assoc();
                        $bonus_percentage = (float)$rule['bonus_percentage'];
                    }

                    // === Deductions ===
                    // SSS
                    $sss = 0;
                    $sss_sql = "SELECT employee_share FROM sss_contributions WHERE ? BETWEEN min_salary_credit AND max_salary_credit LIMIT 1";
                    $sss_stmt = $conn->prepare($sss_sql);
                    $sss_stmt->bind_param("d", $salary);
                    $sss_stmt->execute();
                    $sss_result = $sss_stmt->get_result();
                    if ($sss_result && $sss_result->num_rows > 0) {
                        $sss_row = $sss_result->fetch_assoc();
                        $sss = (float)$sss_row['employee_share'];
                    }
                    $sss_stmt->close();

                    // PhilHealth: 2.25%
                    $philhealth = round($salary * 0.0225, 2);

                    // Pag-IBIG: fixed 100
                    $pagibig = 100;

                    // Tax (TRAIN)
                    $taxable_income = $salary - $sss - $philhealth - $pagibig;
                    $tax = 0;
                    $tax_sql = "SELECT base_tax, marginal_rate, lower_limit FROM income_tax_brackets WHERE ? BETWEEN min_income AND max_income LIMIT 1";
                    $tax_stmt = $conn->prepare($tax_sql);
                    $tax_stmt->bind_param("d", $taxable_income);
                    $tax_stmt->execute();
                    $tax_result = $tax_stmt->get_result();
                    if ($tax_result && $tax_result->num_rows > 0) {
                        $tax_row = $tax_result->fetch_assoc();
                        $excess = $taxable_income - $tax_row['lower_limit'];
                        $tax = $tax_row['base_tax'] + ($tax_row['marginal_rate'] / 100) * $excess;
                    }
                    $tax_stmt->close();

                    $total_deduction = $sss + $philhealth + $pagibig + $tax;
                    $bonus = $salary * ($bonus_percentage / 100);
                    $net_salary = $salary - $total_deduction + $bonus;

                    $salary_fmt = "₱" . number_format($salary, 2);
                    $deduction_fmt = 
                        "SSS: ₱" . number_format($sss, 2) . "<br>" .
                        "PhilHealth: ₱" . number_format($philhealth, 2) . "<br>" .
                        "Pag-IBIG: ₱" . number_format($pagibig, 2) . "<br>" .
                        "Withholding Tax: ₱" . number_format($tax, 2) . "<br>" .
                        "<strong>Total: ₱" . number_format($total_deduction, 2) . "</strong>";
                    $bonus_fmt = "₱" . number_format($bonus, 2) . " (" . number_format($bonus_percentage, 2) . "%)";
                    $net_salary_fmt = "₱" . number_format($net_salary, 2);
                    $full_name = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
                    $manager_id = htmlspecialchars($row['manager_id'] ?? 'N/A');

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($employee_id) . "</td>";
                    echo "<td class='emp-name'>" . $full_name . "</td>";
                    echo "<td>" . htmlspecialchars($row['position'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($row['department_name'] ?? 'N/A') . "</td>";
                    echo "<td>" . $manager_id . "</td>";
                    echo "<td>" . $salary_fmt . "</td>";
                    echo "<td style='font-size: 12px;'>" . $deduction_fmt . "</td>";
                    echo "<td>" . $bonus_fmt . "</td>";
                    echo "<td><strong>" . $net_salary_fmt . "</strong></td>";
                    echo "</tr>";
                }
            } else {
                echo '<tr><td colspan="9" style="text-align:center;">No payroll records found.</td></tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    const searchInput = document.getElementById('attsearch');
    const tableRows = document.querySelectorAll("#payrollTable tbody tr");

    searchInput.addEventListener('input', () => {
        const keyword = searchInput.value.toLowerCase().trim();

        tableRows.forEach(row => {
            const id = row.cells[0].textContent.toLowerCase();
            const name = row.querySelector('.emp-name').textContent.toLowerCase();

            row.style.display = (id.includes(keyword) || name.includes(keyword)) ? '' : 'none';
        });
    });
</script>
</body>
</html>

<?php $conn->close(); ?>
