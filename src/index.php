<?php
$host = 'db';
$user = 'root';
$pass = 'rootpassword';
$db   = 'lampdb';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>LAMP Stack is running!</h1>";
echo "<p>Connected to MySQL successfully.</p>";
?>
