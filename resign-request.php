<?php 
session_start();
include 'connect.php';

if (!isset($_SESSION['email'])) {
    die("Login required.");
}

$email = $_SESSION['email'];

// Get employee ID
$stmt = $conn->prepare("SELECT id, first_name, last_name FROM nexaemp WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($employee_id, $first_name, $last_name);
$stmt->fetch();
$stmt->close();

if (!$employee_id) {
    echo "<script>alert('User not found.'); window.location='login.html';</script>";
    exit();
}

$status = null;
$reason = null;

// Get the latest resignation request (if any)
$check = $conn->prepare("SELECT reason, status FROM resign_requests WHERE employee_id = ? ORDER BY id DESC LIMIT 1");
$check->bind_param("i", $employee_id);
$check->execute();
$check->bind_result($reason, $status);
$check->fetch();
$check->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reason']) && ($status !== 'Pending' && $status !== 'Approved')) {
    $new_reason = trim($_POST['reason']);

    $stmt = $conn->prepare("INSERT INTO resign_requests (employee_id, reason, status) VALUES (?, ?, 'Pending')");
    $stmt->bind_param("is", $employee_id, $new_reason);

    if ($stmt->execute()) {
        echo "<script>alert('Resignation submitted.'); window.location='resign-request.php';</script>";
    } else {
        echo "<script>alert('Error submitting resignation.'); window.location='resign-request.php';</script>";
    }

    $stmt->close();
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Resignation Request</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #eef2f7;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        p strong {
            font-size: 1.1em;
        }

        .status-box {
            border: 1px solid #ccc;
            padding: 20px;
            margin-top: 20px;
            background-color: #f9f9f9;
            border-left: 5px solid #888;
            border-radius: 5px;
        }

        .status-approved {
            color: #28a745;
            font-weight: bold;
        }

        .status-rejected {
            color: #dc3545;
            font-weight: bold;
        }

        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            resize: vertical;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
            margin-top: 20px;
        }

        button {
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0069d9;
        }

        .message {
            font-style: italic;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Resignation Portal</h2>
    <p><strong>Welcome, <?= htmlspecialchars($first_name . ' ' . $last_name) ?></strong></p>

    <?php if ($status): ?>
        <div class="status-box">
            <p><strong>Last Request:</strong></p>
            <p><strong>Reason:</strong> <?= htmlspecialchars($reason) ?></p>
            <p><strong>Status:</strong>
                <?php if ($status === 'Approved'): ?>
                    <span class="status-approved">Approved</span>
                <?php elseif ($status === 'Rejected'): ?>
                    <span class="status-rejected">Rejected</span>
                <?php else: ?>
                    <span class="status-pending">Pending</span>
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if (!$status || $status === 'Rejected'): ?>
        <form method="post">
            <label for="reason">Submit New Resignation Request:</label>
            <textarea name="reason" id="reason" required rows="4" placeholder="Enter your reason for resignation..."></textarea>
            <button type="submit">Submit Resignation</button>
        </form>
    <?php elseif ($status === 'Pending'): ?>
        <p class="message">You have already submitted a resignation request and it is currently pending.</p>
    <?php elseif ($status === 'Approved'): ?>
        <p class="message">Your resignation has been approved. Please follow HR instructions.</p>
    <?php endif; ?>
</div>

</body>
</html>
