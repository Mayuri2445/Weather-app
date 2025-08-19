<?php
$logFile = 'weather_log.json';
$data = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
?>

<!DOCTYPE html>
<html>
<head>
  <title>Weather History</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f8ff;
      margin: 0;
      padding: 0;
    }

    .header {
      background-color: #2196F3;
      padding: 15px 20px;
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header h2 {
      margin: 0;
    }

    .nav-links a {
      color: white;
      text-decoration: none;
      margin-left: 20px;
      font-weight: bold;
    }

    .content {
      padding: 20px;
    }

    table {
      border-collapse: collapse;
      width: 100%;
      background: #ffffff;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }

    th, td {
      border: 1px solid #ccc;
      padding: 12px;
      text-align: center;
    }

    th {
      background: #2196F3;
      color: white;
    }

    tr:nth-child(even) {
      background: #f9f9f9;
    }

    p {
      text-align: center;
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="header">
  <h2>📜 Weather History</h2>
  <div class="nav-links">
    <a href="index.php">🏠 Home</a>
    <a href="admin.php">🔐 Admin</a>
  </div>
</div>

<div class="content">
  <?php if (!empty($data)) { ?>
    <table>
      <tr>
        <th>City</th>
        <th>Temperature (°C)</th>
        <th>Condition</th>
        <th>Humidity (%)</th>
        <th>Wind Speed (m/s)</th>
        <th>Time</th>
      </tr>
      <?php foreach (array_reverse($data) as $entry) { ?>
        <tr>
          <td><?= htmlspecialchars($entry['city']) ?></td>
          <td><?= $entry['temp'] ?></td>
          <td><?= htmlspecialchars($entry['desc']) ?></td>
          <td><?= $entry['humidity'] ?></td>
          <td><?= $entry['wind'] ?></td>
          <td><?= $entry['time'] ?></td>
        </tr>
      <?php } ?>
    </table>
  <?php } else { ?>
    <p>No weather history found.</p>
  <?php } ?>
</div>

</body>
</html>