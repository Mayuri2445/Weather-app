<?php
// File jisme logs save hote hai
$logFile = "weather_log.json";

// Check file exists
if (!file_exists($logFile)) {
    die("No weather history found.");
}

// Get logs
$logs = json_decode(file_get_contents($logFile), true);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="weather_report.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// CSV header row
fputcsv($output, ['City', 'Temperature (°C)', 'Condition', 'Humidity (%)', 'Wind (m/s)', 'Time']);

// CSV data rows
foreach ($logs as $log) {
    fputcsv($output, [
        $log['city'],
        $log['temp'],
        $log['desc'],
        $log['humidity'],
        $log['wind'],
        $log['time']
    ]);
}

// Close output
fclose($output);
exit;
?>
