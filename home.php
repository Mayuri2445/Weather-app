<!DOCTYPE html>
<html>
<head>
  <title>Welcome to Weather App</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #2196f3, #21cbf3);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      text-align: center;
      background: white;
      padding: 50px;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0,0,0,0.2);
      max-width: 400px;
      width: 90%;
    }

    h1 {
      color: #1565c0;
      margin-bottom: 30px;
    }

    .button {
      display: block;
      width: 100%;
      padding: 15px;
      margin: 15px 0;
      font-size: 16px;
      background-color: #1565c0;
      color: white;
      border: none;
      border-radius: 8px;
      text-decoration: none;
      transition: background 0.3s;
    }

    .button:hover {
      background-color: #0d47a1;
    }

    .note {
      margin-top: 20px;
      font-size: 14px;
      color: #555;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>🌤 Weather Detection System</h1>
    <a href="user_login.php" class="button">👤 User Login</a>
    <a href="admin.php" class="button">🔐 Admin Login</a>
    <p class="note">Choose your role to proceed</p>
  </div>
</body>
</html>