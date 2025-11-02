<?php
require_once 'config.php';

// Check if a user is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['class_id'])) {
    // If logged in, send them directly to their subjects
    header('Location: subjects.php');
    exit();
} else {
    // If not logged in, show the login page
    header('Location: login.php');
    exit();
}