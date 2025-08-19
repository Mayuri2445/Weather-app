<?php
session_start();
include 'config.php'; // DB connection

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $db_password);
        $stmt->fetch();

        // Since your passwords are plain text:
        if ($password === $db_password) {
            $_SESSION['user_id'] = $id;
            header("Location: index.php");
            exit();
        } else {
            $msg = "❌ Incorrect password.";
        }
    } else {
        $msg = "❌ Email not found.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <style>
        body {
            font-family: Arial;
            background: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        form {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px #aaa;
        }
        input {
            display: block;
            margin-bottom: 15px;
            padding: 10px;
            width: 250px;
        }
        button {
            padding: 10px;
            background: #2196F3;
            color: #fff;
            border: none;
            width: 100%;
        }
        .msg {
            color: red;
        }
    </style>
</head>
<body>
    <form method="post">
        <h2>User Login</h2>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
        <p class="msg"><?= $msg ?></p>
    </form>
</body>
</html>