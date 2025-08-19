<?php
require 'db.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

$token = $_GET['token'] ?? '';
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $token = $_POST['token'];

    if ($password !== $confirm) {
        $msg = "❌ Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $msg = "❌ Password must be at least 6 characters.";
    } else {
        $new_pass = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE reset_token = ? AND token_expiry > NOW()");
        $stmt->execute([$token]);

        if ($stmt->rowCount() > 0) {
            $update = $pdo->prepare("UPDATE admin_users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
            $update->execute([$new_pass, $token]);
            $msg = "✅ Password updated successfully. <a href='admin.php'>Login</a>";
        } else {
            $msg = "❌ Invalid or expired token.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Reset Password</title>
  <style>
    body { font-family: Arial; background: #f4f4f4; padding: 50px; }
    .container { background: white; padding: 30px; border-radius: 10px; width: 300px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    input { width: 100%; padding: 10px; margin: 10px 0; }
    button { padding: 10px; width: 100%; background: #28a745; color: white; border: none; border-radius: 5px; }
    p { margin-top: 15px; }
  </style>
</head>
<body>
  <div class="container">
    <h2>🔁 Reset Password</h2>
    <form method="post">
      <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
      <input type="password" name="password" placeholder="Enter new password" required>
      <input type="password" name="confirm_password" placeholder="Confirm password" required>
      <button type="submit">Update Password</button>
    </form>
    <p><?php echo htmlspecialchars($msg); ?></p>
  </div>
</body>
</html>