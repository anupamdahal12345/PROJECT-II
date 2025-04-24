<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // If you set a password for MySQL, add it here
$db = 'salon_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
