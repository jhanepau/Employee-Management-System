<?php
session_start();
include 'connect.php';

// Only the hardcoded master admin can access this page
if (!isset($_SESSION['email']) || $_SESSION['email'] !== 'admin@admin.nexatech.com') {
    die("Access denied. Admins only.");
}

// Fetch all pending users
$sql = "
SELECT 
  n.id, n.first_name, n.last_name, n.email, n.role, 
  et.type_name AS employee_type, 
  d.department_name AS department, 
  j.job_title AS job,
  n.birthday 
FROM nexaemp n
LEFT JOIN employee_types et ON n.employee_type_id = et.id
LEFT JOIN departments d ON n.department_id = d.id
LEFT JOIN jobs j ON n.job_id = j.id
WHERE n.is_approved = 0
";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin — Approve New Accounts</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 2rem; }
    .background {
      background-image: url("bghome.png"); /* Replace with your image path */
      background-size: cover;
      background-position: center;
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
    }

    h1 { text-align: center; }
    table { width: 100%; border-collapse: collapse; background: #fff; }
    th, td { padding: 8px 12px; border: 1px solid #ccc; text-align: left; }
    th { background: #333; color: #fff; }
    form { display: inline; }
    button { 
      padding: 6px 12px; 
      border: none; 
      color: #fff; 
      cursor: pointer; 
      border-radius: 4px;
    }
    .approve-btn { background: #28a745; }
    .approve-btn:hover { background: #218838; }
    .remove-btn { background: #dc3545; }
    .remove-btn:hover { background: #c82333; }
    .no-data { text-align: center; margin-top: 2rem; }
  </style>
</head>
<body>

  <h1>Pending Account Approvals</h1>

  <?php if ($result && $result->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Type</th>
          <th>Department</th>
          <th>Job</th>
          <th>Birthday</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($user = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
          <td><?= htmlspecialchars($user['email']) ?></td>
          <td><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
          <td><?= htmlspecialchars($user['employee_type']) ?></td>
          <td><?= htmlspecialchars($user['department']) ?></td>
          <td><?= htmlspecialchars($user['job']) ?></td>
          <td><?= htmlspecialchars($user['birthday']) ?></td>
          <td>
            <form method="POST" action="approve.php">
              <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
              <button type="submit" class="approve-btn">Approve</button>
            </form>
            <form method="POST" action="remove.php" onsubmit="return confirm('Are you sure you want to delete this user?');">
              <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
              <button type="submit" class="remove-btn">Remove</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="no-data">No pending accounts to approve.</p>
  <?php endif; ?>

</body>
</html>

<?php
$conn->close();
?>