<?php
require('fpdf.php');
include "connect.php";

$employee_id = $_POST['employee_id'] ?? 'N/A';
$employee_name = $_POST['employee_name'] ?? 'N/A';
$job_title = $_POST['job_title'] ?? 'N/A';
$base_salary = floatval($_POST['base_salary'] ?? 0.0);
$bonus = floatval($_POST['bonus'] ?? 0.0);

// --- Calculate SSS deduction ---
$sss_deduction = 0.0;
$sql = "SELECT employee_share FROM sss_contributions WHERE ? BETWEEN min_salary_credit AND max_salary_credit LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('d', $base_salary);
$stmt->execute();
$stmt->bind_result($employee_share);
if ($stmt->fetch()) {
    $sss_deduction = (float)$employee_share;
} else {
    $sss_deduction = 500.00;  // fallback
}
$stmt->close();

// --- Calculate PhilHealth deduction ---
// Base salary capped between 10,000 and 80,000 for PhilHealth contribution
$philhealth_base = min(max($base_salary, 10000), 80000);
$philhealth_deduction = $philhealth_base * 0.05625;  // 5.625% employee share

// --- Pag-IBIG deduction ---
$pagibig_deduction = 100.00;  // fixed

// --- Calculate income tax deduction based on TRAIN law brackets ---
$tax_deduction = 0.0;
$sql_tax = "SELECT base_tax, marginal_rate, lower_limit FROM income_tax_brackets WHERE ? BETWEEN min_income AND max_income LIMIT 1";
$stmt_tax = $conn->prepare($sql_tax);
$stmt_tax->bind_param('d', $base_salary);
$stmt_tax->execute();
$stmt_tax->bind_result($base_tax, $marginal_rate, $lower_limit);
if ($stmt_tax->fetch()) {
    $taxable_income = max(0, $base_salary - $lower_limit);
    $tax_deduction = $base_tax + ($taxable_income * ($marginal_rate / 100));
} else {
    $tax_deduction = 0.0;
}
$stmt_tax->close();

// --- Total deductions ---
$total_deductions = $sss_deduction + $philhealth_deduction + $pagibig_deduction + $tax_deduction;

// --- Calculate net pay ---
$total_salary = $base_salary + $bonus - $total_deductions;

// --- Generate PDF payroll slip ---
$pay_period = date('F Y');  
$issue_date = date('Y-m-d');

$pdf = new FPDF('P','mm',[130,210]);  // Custom slip size
$pdf->AddPage();

$pdf->Image('1_nexatech.png', 5, 5, 20);
$pdf->SetFont('Arial','B',10);
$pdf->SetXY(30, 8);
$pdf->Cell(0, 5, 'NexaTech Corporation', 0, 1);
$pdf->SetFont('Arial','',8);
$pdf->SetX(30);
$pdf->MultiCell(0, 4, "1234 Business Ave\nCity, Country\nTel: (123) 456-7890");

$pdf->Ln(3);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'PAYROLL SLIP',0,1,'C');

$pdf->SetFont('Arial','',9);
$pdf->Cell(0,5,"Pay Period: $pay_period",0,1,'C');
$pdf->Cell(0,5,"Date Issued: $issue_date",0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('Arial','B',9);
$pdf->Cell(30,6,'Employee ID:',0,0);
$pdf->SetFont('Arial','',9);
$pdf->Cell(0,6,$employee_id,0,1);

$pdf->SetFont('Arial','B',9);
$pdf->Cell(30,6,'Name:',0,0);
$pdf->SetFont('Arial','',9);
$pdf->Cell(0,6,$employee_name,0,1);

$pdf->SetFont('Arial','B',9);
$pdf->Cell(30,6,'Position:',0,0);
$pdf->SetFont('Arial','',9);
$pdf->Cell(0,6,$job_title,0,1);

$pdf->Ln(5);

$pdf->SetFont('Arial','',9);
// Centered amounts: use full width cells with alignment 'C' or 'R' as needed
$pdf->Cell(60,7,'Base Salary',0,0,'L');
$pdf->Cell(50,7,'PHP ' . number_format($base_salary, 2),0,1,'R');

$pdf->Cell(60,7,'Bonus',0,0,'L');
$pdf->Cell(50,7,'PHP ' . number_format($bonus, 2),0,1,'R');

$pdf->Ln(3);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(0,7,'Deductions:',0,1);

$pdf->SetFont('Arial','',9);
$pdf->Cell(60,7,'- SSS',0,0,'L');
$pdf->Cell(50,7,'PHP ' . number_format($sss_deduction, 2),0,1,'R');

$pdf->Cell(60,7,'- PhilHealth',0,0,'L');
$pdf->Cell(50,7,'PHP ' . number_format($philhealth_deduction, 2),0,1,'R');

$pdf->Cell(60,7,'- Pag-IBIG',0,0,'L');
$pdf->Cell(50,7,'PHP ' . number_format($pagibig_deduction, 2),0,1,'R');

$pdf->Cell(60,7,'- Tax',0,0,'L');
$pdf->Cell(50,7,'PHP ' . number_format($tax_deduction, 2),0,1,'R');

$pdf->Ln(5);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(60,8,'Net Pay',0,0,'L');
$pdf->Cell(50,8,'PHP ' . number_format($total_salary, 2),0,1,'R');

$pdf->Ln(10);

$pdf->SetFont('Arial','',9);
$pdf->Cell(0,6,"Authorized Signature:",0,1);
$pdf->Cell(0,10,'__________________________',0,1,'L');

$pdf->Ln(5);

$pdf->SetFont('Arial','I',7);
$pdf->MultiCell(0,4,"Thank you for your hard work!\nThis is a computer-generated document and does not require a signature.");

$pdf->Output('D', "Payroll_Slip_{$employee_id}_{$pay_period}.pdf");

$conn->close();
?>