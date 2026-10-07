<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.html");
    exit;
}

$employee_id = $_SESSION['id'];

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $file_name = time() . "_" . basename($_FILES["profile_picture"]["name"]);
    $target_file = $target_dir . $file_name;
    $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Allow only jpg, jpeg, png
    if (in_array($image_file_type, ["jpg", "jpeg", "png"])) {
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $sql = "UPDATE nexaemp SET profile_picture = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $target_file, $employee_id);
            $stmt->execute();
            $stmt->close();
            header("Location: profile.php");
            exit;
        } else {
            echo "<script>alert('Error uploading image.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Only JPG, JPEG, and PNG files are allowed.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('No image selected.'); window.history.back();</script>";
}
?>
