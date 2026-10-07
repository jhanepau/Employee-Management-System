<?php 
session_start();
include 'connect.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.html");
    exit;
}

$employee_id = $_SESSION['id'];

$sql = "SELECT profile_picture 
        FROM nexaemp WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$stmt->bind_result($profile_picture);
$stmt->fetch();
$stmt->close();


$image_path = $profile_picture ? $profile_picture : "unknown.jpg";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="profile.css">
    <title>Profile</title>
</head>
<body>
    <div class="container">
        <img src="1_nexatech.png" class="logo">
        <nav>
            <ul>
                <li><a href="homeMenu.html">Home</a></li>
                <li>Help</li>
                <li>About Us</li>
                <li>Profile</li>
                <li><a href="logout.php">Exit</a></li>
            </ul>
        </nav>
    </div>
    
    <div class="wrapper">
        <div class="left">

            <img src="<?= htmlspecialchars($image_path) ?>" width="100" height="100"
            style="object-fit: cover; border: 1px solid #ccc;">
            <form action="upload_picture.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="profile_picture" accept="image/*" capture="environment" required>
                <button type="submit">Upload</button>
            </form>

            <h4>Employee ID: <?= htmlspecialchars($employee_id) ?></h4>
            <p>Position: </p>
            <p>Department: </p>
        </div>

        <div class="right">
            <div class="info">
                <h3></h3>
                <h4>NAME</h4>

                <h3></h3>
                <h4>EMAIL</h4>

                <h3> </h3>
                <h4>BIRTHDATE</h4>

                <h3></h3>
                <h4>AGE</h4>

                <h3></h3>
                <h4>HIRE DATE</h4>

                <h3></h3>
                <h4>END DATE</h4>
            </div>
        </div>
    </div>
</body>
</html>
