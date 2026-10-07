<?php
session_start(); // REQUIRED to use $_SESSION

// DB connection parameters
$host = 'localhost';
$dbname = 'nexatech';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch employee types (IDs and names)
    $stmt = $pdo->query("SELECT id, type_name FROM employee_types ORDER BY type_name");
    $employee_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch departments (IDs and names)
    $stmt = $pdo->query("SELECT id, department_name FROM departments ORDER BY department_name");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch jobs with department names
    $stmt = $pdo->query("
        SELECT j.id AS job_id, j.job_title, d.id AS department_id, d.department_name 
        FROM jobs j 
        JOIN departments d ON j.department_id = d.id
        ORDER BY d.department_name, j.job_title
    ");
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group jobs by department for display
    $jobs_by_department = [];
    foreach ($jobs as $job) {
        $jobs_by_department[$job['department_name']][] = $job;
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $employee_type_id = $_POST['employee_type_id']; // ✔ match form field
    $department_id = $_POST['department_id'];       // ✔ match form field
    $job_id = $_POST['job_id'];                     // ✔ match form field
    $birthday = $_POST['birthday'];

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
        exit();
    }
    if (strlen($password) !== 8) {
        echo "<script>alert('Password must be exactly 8 characters long.'); window.history.back();</script>";
        exit();
    }

    if (str_ends_with($email, '@admin.nexatech.com')) {
        $role = 'admin';
    } elseif (str_ends_with($email, '@nexatech.com')) {
        $role = 'employee';
    } else {
        echo "<script>alert('Invalid email domain. Only @nexatech.com and @admin.nexatech.com are allowed.'); window.history.back();</script>";
        exit();
    }

    $_SESSION['signup_data'] = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'password' => $password,
        'role' => $role,
        'employee_type_id' => $employee_type_id,
        'department_id' => $department_id,
        'job_id' => $job_id,
        'birthday' => $birthday
    ];

    $_SESSION['captcha_code'] = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 6);

    header("Location: captcha-verify.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sign Up Form</title>
  <link rel="stylesheet" href="signup.css" />
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
  <img src="1_nexatech.png" alt="Nexatech Logo" />
  <div class="signup">
    <form action="" method="post">
      <h1>Sign Up</h1>

      <div class="form">
        <div class="left dic"> <!-- 👈 KEEPING YOUR LAYOUT CLASS -->
          <div class="input-box">
            <input type="text" name="first_name" placeholder="First Name" required />
            <i class='bx bxs-user'></i>
          </div>

          <div class="input-box">
            <input type="text" name="last_name" placeholder="Last Name" required />
            <i class='bx bxs-user'></i>
          </div>

          <div class="date-wrapper">
            <input type="date" name="birthday" placeholder="Birthday" required />
          </div>

          <div class="input-box">
            <input type="email" name="email" placeholder="Email" required />
            <i class='bx bxs-envelope'></i>
          </div>

          <div class="input-box">
            <input type="password" name="password" placeholder="Password" required />
            <i class='bx bxs-lock-alt'></i>
          </div>

          <div class="input-box">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required />
            <i class='bx bxs-lock-alt'></i>
          </div>
        </div>

        <div class="right dic"> <!-- 👈 KEEPING YOUR LAYOUT CLASS -->
          <select name="employee_type_id" required>
            <option value="" disabled selected>Select Employee Type</option>
            <?php foreach ($employee_types as $type): ?>
              <option value="<?= htmlspecialchars($type['id']) ?>"><?= htmlspecialchars($type['type_name']) ?></option>
            <?php endforeach; ?>
          </select>

          <select name="department_id" required>
            <option value="" disabled selected>Select Department</option>
            <?php foreach ($departments as $dept): ?>
              <option value="<?= htmlspecialchars($dept['id']) ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
            <?php endforeach; ?>
          </select>

          <select name="job_id" required>
            <option value="" disabled selected>Select Job</option>
            <?php foreach ($jobs_by_department as $dept_name => $jobs): ?>
              <optgroup label="<?= htmlspecialchars($dept_name) ?>">
                <?php foreach ($jobs as $job): ?>
                  <option value="<?= htmlspecialchars($job['job_id']) ?>"><?= htmlspecialchars($job['job_title']) ?></option>
                <?php endforeach; ?>
              </optgroup>
            <?php endforeach; ?>
          </select>

          <i class='bx bxs-briefcase'></i>
        </div>
      </div>

      <button type="submit" class="btn">Sign Up</button>

      <div class="login-link">
        <p>Already have an account? <a href="login.html">Login</a></p>
      </div>
    </form>
  </div>
</body>
</html>