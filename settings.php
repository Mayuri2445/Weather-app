<?php
session_start();

// Settings file
$settingsFile = 'settings.json';
if (!file_exists($settingsFile)) {
    $defaultSettings = [
        'openWeatherApiKey' => 'dfdeeea3e401405c92306186c8fc64ef',
        'timezone' => 'Asia/Kolkata',
        'theme' => 'light' // default theme
    ];
    file_put_contents($settingsFile, json_encode($defaultSettings, JSON_PRETTY_PRINT));
}
$settings = json_decode(file_get_contents($settingsFile), true);

// Logout handling
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: settings.php");
    exit;
}

// Login handling
$loginError = '';
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Simple hardcoded admin check (change as needed)
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user_role'] = 'admin';
        header("Location: settings.php");
        exit;
    } else {
        $loginError = "❌ Invalid username or password!";
    }
}

// Settings form handling
$message = '';
if (isset($_POST['save_settings']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $settings['openWeatherApiKey'] = htmlspecialchars($_POST['openWeatherApiKey']);
    $settings['timezone'] = htmlspecialchars($_POST['timezone']);
    $settings['theme'] = htmlspecialchars($_POST['theme']); // save theme
    file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
    $message = "✅ Settings updated successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Settings - Weather Project</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #eef5ff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; margin-bottom: 20px; }
        input[type=text], input[type=password], select {
            width: 100%;
            padding: 12px;
            margin: 8px 0 15px 0;
            border: 2px solid #4a90e2;
            border-radius: 5px;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover { background: #357ABD; }
        .message { color: green; text-align: center; margin-bottom: 15px; }
        .error { color: red; text-align: center; margin-bottom: 15px; }
        .logout { text-align: center; margin-top: 15px; }
        .logout a { color: #4a90e2; text-decoration: none; }
    </style>
</head>
<body>

<div class="container">
    <?php if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
        <h2>Admin Login</h2>
        <?php if($loginError) echo "<div class='error'>$loginError</div>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>

    <?php else: ?>
        <h2>⚙ Settings</h2>
        <?php if($message) echo "<div class='message'>$message</div>"; ?>
        <form method="post">
            <label>OpenWeatherMap API Key:</label>
            <input type="text" name="openWeatherApiKey" value="<?= htmlspecialchars($settings['openWeatherApiKey']) ?>" required>

            <label>Timezone:</label>
            <select name="timezone" required>
                <?php
                $timezones = DateTimeZone::listIdentifiers();
                foreach($timezones as $tz){
                    $selected = ($tz === $settings['timezone']) ? 'selected' : '';
                    echo "<option value='$tz' $selected>$tz</option>";
                }
                ?>
            </select>

            <label>Theme:</label>
            <select name="theme" required>
                <option value="light" <?= ($settings['theme'] === 'light') ? 'selected' : '' ?>>Light</option>
                <option value="dark" <?= ($settings['theme'] === 'dark') ? 'selected' : '' ?>>Dark</option>
            </select>

            <button type="submit" name="save_settings">Save Settings</button>
        </form>
        <div class="logout">
            <a href="settings.php?logout=1">Logout</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>