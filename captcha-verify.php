<?php
session_start();

if (!isset($_SESSION['signup_data'])) {
    echo "<script>
            alert('No signup data found. Please go back to the signup page.');
            window.location.href = 'signup.php';
          </script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['captcha']) || $_POST['captcha'] !== $_SESSION['captcha_code']) {
        echo "<script>
                alert('Incorrect CAPTCHA. Try again.');
                window.location.href = 'captcha-verify.php';
              </script>";
        exit();
    }

    $first_name = $_SESSION['signup_data']['first_name'];
    $last_name = $_SESSION['signup_data']['last_name'];
    $email = $_SESSION['signup_data']['email'];
    $password = password_hash($_SESSION['signup_data']['password'], PASSWORD_DEFAULT);
    $role = $_SESSION['signup_data']['role'];
    $employee_type_id = (int)$_SESSION['signup_data']['employee_type_id'];
    $department_id = (int)$_SESSION['signup_data']['department_id'];
    $job_id = (int)$_SESSION['signup_data']['job_id'];
    $birthday = $_SESSION['signup_data']['birthday'];
    $is_approved = 0;

    $conn = new mysqli("localhost", "root", "", "nexatech");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Validate foreign keys before insert
    $res = $conn->query("SELECT id FROM employee_types WHERE id = $employee_type_id");
    if ($res->num_rows == 0) {
        $employee_type_id = null;
    }

    $res = $conn->query("SELECT id FROM departments WHERE id = $department_id");
    if ($res->num_rows == 0) {
        $department_id = null;
    }

    $res = $conn->query("SELECT id FROM jobs WHERE id = $job_id");
    if ($res->num_rows == 0) {
        $job_id = null;
    }

    // Prepare statement — for nullable INTs, use NULLABLE params workaround
    $stmt = $conn->prepare("INSERT INTO nexaemp 
        (first_name, last_name, email, password, role, employee_type_id, department_id, job_id, birthday, is_approved) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Use variables for binding — if null, bind as null by using mysqli_stmt::bind_param doesn't accept PHP null for ints,
    // so set null IDs to 0 or you can use NULL and SQL will convert if columns allow NULLs.
    $employee_type_id_param = $employee_type_id ?: null;
    $department_id_param = $department_id ?: null;
    $job_id_param = $job_id ?: null;

    // Bind parameters with s = string, i = integer
    // To allow nulls for ints, use a workaround by passing null as NULL in SQL
    $stmt->bind_param(
        "ssssssssis",
        $first_name,
        $last_name,
        $email,
        $password,
        $role,
        $employee_type_id_param,
        $department_id_param,
        $job_id_param,
        $birthday,
        $is_approved
    );

    if ($stmt->execute()) {
        unset($_SESSION['signup_data']);
        unset($_SESSION['captcha_code']);
        echo "<script>
                alert('Signup successful! Your account is pending admin approval.');
                window.location.href = 'login.html';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('Failed to save data. Try again.');
                window.location.href = 'signup.php';
              </script>";
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>

<!-- HTML CAPTCHA Form -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>CAPTCHA Verification</title>
  <link rel="stylesheet" href="captcha.css" />
</head>
<body>
  <div class="captcha-container">
    <form action="captcha-verify.php" method="POST">
      <h1>CAPTCHA Verification</h1>
      <p>Please enter the CAPTCHA code below:</p>
      <div class="captcha-code">
        <strong><?= isset($_SESSION['captcha_code']) ? $_SESSION['captcha_code'] : '' ?></strong>
      </div>
      <div class="input-box">
        <input type="text" name="captcha" placeholder="Enter CAPTCHA" required />
      </div>
      <button type="submit" class="btn">Verify</button>
    </form>
  </div>
</body>
</html>