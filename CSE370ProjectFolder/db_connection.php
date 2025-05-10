<?php
$host = "localhost";         // or your server name
$username = "root";          // your MySQL username
$password = "";              // your MySQL password
$database = "CSE370";        // your database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
