<?php
require 'config.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $stmt = $conn->prepare("UPDATE admin_users SET reset_token = ?, token_expiry = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expiry, $email);
        $stmt->execute();

        $reset_link = "http://localhost/weather-app/reset_password.php?token=$token";

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'mayuriboharpi@gmail.com';
            $mail->Password   = 'akoj umrz oxxr dowc';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('mayuriboharpi@gmail.com', 'Weather App');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password';
            $mail->Body    = "Click here to reset your password: <a href='$reset_link'>$reset_link</a>";

            $mail->send();
            $msg = "<p class='success'>✅ Reset link sent to your email.</p>";
        } catch (Exception $e) {
            $msg = "<p class='error'>❌ Email could not be sent. Error: {$mail->ErrorInfo}</p>";
        }
    } else {
        $msg = "<p class='error'>❌ Email not found.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | Weather App</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #83a4d4, #b6fbff);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .box {
            background: white;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        input[type="email"] {
            width: 90%;
            padding: 10px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        button {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #1976D2;
        }

        .success {
            color: green;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .error {
            color: red;
            margin-bottom: 10px;
            font-weight: bold;
        }

        @media screen and (max-width: 500px) {
            .box {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

<div class="box">
    <h2>🔐 Forgot Password</h2>
    <?= $msg ?>
    <form method="post">
        <input type="email" name="email" placeholder="Enter your email" required>
        <br>
        <button type="submit">Send Reset Link</button>
    </form>
</div>

</body>
</html>