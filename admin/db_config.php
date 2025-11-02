<?php
// --- DATABASE CONNECTION --
// IMPORTANT: Replace with your actual database credentials!
$servername = "sql309.byetcluster.com"; // Or your host
$username = "if0_40143237";
$password = "DflWGeTjaLqb"; // Change this
$dbname = "if0_40143237_classmateapp";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>