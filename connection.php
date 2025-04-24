<?php
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'salon_db';

// Establish the connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
