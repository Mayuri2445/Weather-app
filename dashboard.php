<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: admin.php");
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <style>
    body {
      font-family: Arial;
      background: #f7f9fc;
      padding: 20px;
    }
    .logout-btn {
      background: red;
      color: white;
      border: none;
      padding: 10px;
      border-radius: 5px;
      float: right;
    }
  </style>
</head>
<body>

  <h2>📊 Welcome, Admin!</h2>
  <form action="logout.php" method="post">
    <button type="submit" name="logout" class="logout-btn">Logout</button>
  </form>

  <p>✅ You are now logged in and can manage your weather data.</p>

</body>
</html>