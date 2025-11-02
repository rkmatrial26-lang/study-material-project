<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

$db_host = 'sql309.infinityfree.com';
$db_user = 'if0_40143237';
$db_pass = 'DflWGeTjaLqb';
$db_name = 'if0_40143237_classmateapp';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>