<?php
session_start();
include 'connect.php';

$email = $_SESSION['email'];

$sql = "
SELECT 
    e.id AS employee_id,
    CONCAT(e.first_name, ' ', e.last_name) AS full_name,
    j.job_title,
    j.min_salary,
    j.max_salary,
    e.employee_type_id
FROM nexaemp e
LEFT JOIN jobs j ON e.job_id = j.id
WHERE e.email = ? AND e.is_deleted = 0 AND e.is_approved = 1
LIMIT 1;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Invalid employee ID or not approved.'); window.location.href='login.html';</script>";
    exit();
}

$row = $result->fetch_assoc();

// Calculate base salary as average of min and max salary
$min_salary = $row['min_salary'] ?? 20000;
$max_salary = $row['max_salary'] ?? 30000;
$base_salary = ($min_salary + $max_salary) / 2;

// -------------- FUNCTIONS --------------

// Calculate SSS employee contribution
function calculateSSS($salary, $conn) {
    $sql = "SELECT employee_share FROM sss_contributions WHERE ? BETWEEN min_salary_credit AND max_salary_credit LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("d", $salary);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        return (float)$row['employee_share'];
    }
    return 0;
}

// Calculate PhilHealth contribution (2.25% of monthly salary base, min 10k max 80k)
function calculatePhilHealth($salary) {
    $base = max(10000, min(80000, $salary));
    return round($base * 0.0225, 2);
}

// Pag-IBIG fixed employee contribution
function calculatePagIBIG() {
    return 100.00;
}

// Calculate Income Tax based on TRAIN law brackets
function calculateIncomeTax($taxable_income, $conn) {
    $sql = "SELECT * FROM income_tax_brackets WHERE ? BETWEEN min_income AND max_income LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("d", $taxable_income);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $tax = $row['base_tax'] + (($taxable_income - $row['lower_limit']) * ($row['marginal_rate'] / 100));
        return max(0, round($tax, 2));
    }
    return 0;
}

// Get bonus from payroll_rules (based on employee_type and salary range)
function getBonus($employee_type_id, $base_salary, $conn) {
    $sql = "SELECT bonus_percentage FROM payroll_rules WHERE employee_type_id = ? AND ? BETWEEN min_salary AND max_salary LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("id", $employee_type_id, $base_salary);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        return $base_salary * ($row['bonus_percentage'] / 100);
    }
    return 0;
}

// -------------- END FUNCTIONS --------------

// Calculate mandatory contributions
$sss = calculateSSS($base_salary, $conn);
$philhealth = calculatePhilHealth($base_salary);
$pagibig = calculatePagIBIG();

// Taxable income = base salary - mandatory contributions (SSS + PhilHealth + Pag-IBIG)
$taxable_income = $base_salary - ($sss + $philhealth + $pagibig);

// Calculate income tax
$income_tax = calculateIncomeTax($taxable_income, $conn);

// Get bonus amount
$bonus = getBonus($row['employee_type_id'], $base_salary, $conn);

// Sum all deductions
$total_deductions = $sss + $philhealth + $pagibig + $income_tax;

// Prepare deductions breakdown for display
$deductions_breakdown = [
    ['name' => 'SSS Contribution', 'amount' => $sss],
    ['name' => 'PhilHealth Contribution', 'amount' => $philhealth],
    ['name' => 'Pag-IBIG Contribution', 'amount' => $pagibig],
    ['name' => 'Income Tax', 'amount' => $income_tax],
];

// Calculate total salary after deductions and bonus
$total_salary = $base_salary + $bonus - $total_deductions;

// Insert or update employee_deductions for each deduction
$pay_period_start = date('Y-m-01');
$pay_period_end = date('Y-m-t');

foreach ($deductions_breakdown as $ded) {
    $ded_name = $ded['name'];

    // Find deduction_id by name from deductions table
    $deduction_query = $conn->prepare("SELECT id FROM deductions WHERE deduction_name = ? LIMIT 1");
    $deduction_query->bind_param("s", $ded_name);
    $deduction_query->execute();
    $ded_result = $deduction_query->get_result();
    if ($ded_result && $ded_row = $ded_result->fetch_assoc()) {
        $deduction_id = $ded_row['id'];
        
        // Insert or update employee_deductions
        $stmt2 = $conn->prepare("
            INSERT INTO employee_deductions (employee_id, deduction_id, pay_period_start, pay_period_end, amount)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE amount = ?
        ");
        $stmt2->bind_param("iissdd", $row['employee_id'], $deduction_id, $pay_period_start, $pay_period_end, $ded['amount'], $ded['amount']);
        $stmt2->execute();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Payroll</title>
    <link rel="stylesheet" href="payrollemp.css">
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
                <li><a href="logout.php">Exit</a></li>
            </ul>
        </nav>
    </div>

    <div class="report">
        <h1>Payroll</h1>
        <hr class="line">
        <h3>Employee ID: <?= htmlspecialchars($row['employee_id']) ?></h3>
        <h3>Employee Name: <?= htmlspecialchars($row['full_name']) ?></h3>
        <h3>Position: <?= htmlspecialchars($row['job_title'] ?? 'N/A') ?></h3>
        <hr class="line">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Base Salary</th>
                        <th>Bonus</th>
                        <th colspan="2">Deductions Breakdown</th>
                        <th>Total Deduction</th>
                        <th>Total Pay</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>₱ <?= number_format($base_salary, 2) ?></td>
                        <td>₱ <?= number_format($bonus, 2) ?></td>
                        <td colspan="2">
                            <ul>
                                <?php foreach ($deductions_breakdown as $ded) : ?>
                                    <li><?= htmlspecialchars($ded['name']) ?>: ₱ <?= number_format($ded['amount'], 2) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                        <td>₱ <?= number_format($total_deductions, 2) ?></td>
                        <td>₱ <?= number_format($total_salary, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <form method="post" action="generate_receipt.php" target="_blank">
                <input type="hidden" name="employee_id" value="<?= htmlspecialchars($row['employee_id']) ?>">
                <input type="hidden" name="employee_name" value="<?= htmlspecialchars($row['full_name']) ?>">
                <input type="hidden" name="job_title" value="<?= htmlspecialchars($row['job_title'] ?? 'N/A') ?>">
                <input type="hidden" name="base_salary" value="<?= $base_salary ?>">
                <input type="hidden" name="bonus" value="<?= $bonus ?>">
                <input type="hidden" name="total_deductions" value="<?= $total_deductions ?>">
                <input type="hidden" name="total_salary" value="<?= $total_salary ?>">
                <?php
                foreach ($deductions_breakdown as $index => $ded) {
                    echo '<input type="hidden" name="deductions[' . $index . '][name]" value="' . htmlspecialchars($ded['name']) . '">';
                    echo '<input type="hidden" name="deductions[' . $index . '][amount]" value="' . $ded['amount'] . '">';
                }
                ?>
                <button type="submit">Generate PDF</button>
            </form>
        </div>
    </div>
</body>
</html>