<?php 
session_start();
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $hardcoded_admin_email = "admin@admin.nexatech.com";
    $hardcoded_admin_password = "admin123"; // NOTE: Ideally, hash this too in production!

    // Hardcoded admin login (bypass DB)
    if ($email === $hardcoded_admin_email && $password === $hardcoded_admin_password) {
        $_SESSION['id'] = 1; // optional dummy ID for admin
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'admin';
        header("Location: homeMenu.html");
        exit();
    }

    // Prepare query to fetch user by email
    $stmt = $conn->prepare("SELECT * FROM nexaemp WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password with hashed password in DB
        if (password_verify($password, $user['password'])) {

            // Check if user is approved (optional, only if you have this column in your table)
            if (isset($user['is_approved']) && (int)$user['is_approved'] !== 1) {
                echo "<script>alert('Your account is awaiting admin approval.'); window.location.href='login.html';</script>";
                exit();
            }

            // ✅ Set required session variables
            $_SESSION['id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: homeMenu.html");
            } elseif ($user['role'] === 'employee') {
                header("Location: homeMenuEMP.html");
            } else {
                echo "<script>alert('Unknown user role.'); window.location.href='login.html';</script>";
            }
            exit();

        } else {
            // Password mismatch
            echo "<script>alert('Invalid email or password.'); window.location.href='login.html';</script>";
            exit();
        }
    } else {
        // No user found with that email
        echo "<script>alert('Invalid email or password.'); window.location.href='login.html';</script>";
        exit();
    }

    $stmt->close();
}
$conn->close();
?>