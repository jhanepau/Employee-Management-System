<?php
session_start();
include 'connect.php';

if ($_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

$res = $conn->query("SELECT * FROM resign_requests ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head><title>Resignation Management</title></head>
<body>
    <h2>Resignation Requests</h2>
    <table border="1">
        <tr><th>Email</th><th>Reason</th><th>Status</th><th>Action</th></tr>
        <?php while ($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['reason']) ?></td>
            <td><?= $row['status'] ?></td>
            <td>
                <?php if ($row['status'] === 'Pending'): ?>
                <form method="post" action="resign-action.php">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button name="action" value="approve">Approve</button>
                    <button name="action" value="reject">Reject</button>
                </form>
                <?php else: ?>
                    <?= $row['status'] ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
