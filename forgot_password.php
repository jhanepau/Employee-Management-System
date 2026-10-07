<!DOCTYPE html>
<html>
<head>
  <title>Forgot Password</title>
</head>
<body>
  <h2>Forgot Password</h2>
  <form action="send_code.php" method="POST">
    <label>Nexatech Email:</label><br>
    <input type="email" name="nexatech_email" required><br><br>

    <label>Personal Email (where code will be sent):</label><br>
    <input type="email" name="personal_email" required><br><br>

    <button type="submit">Send Reset Code</button>
  </form>
</body>
</html>