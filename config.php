<?php
// API key for weather
$apiKey = "dfdeeea3e401405c92306186c8fc64ef";

date_default_timezone_set('Asia/Kolkata');

// Database configuration
$host = "localhost";
$dbname = "weather_project";
$username = "root";
$password = "";

// Create DB connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}

// Optional: Set charset
$conn->set_charset("utf8mb4");
?>