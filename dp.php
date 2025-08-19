<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // default XAMPP password is blank
$db = 'weather_app'; // replace with your actual DB name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>