<?php
require_once 'config.php';

// Destroy the session
session_unset();
session_destroy();

// Start a new session just to show a message if needed
session_start();

// If the action is 'switch', we don't need a message, just log out and go to login
if (isset($_GET['action']) && $_GET['action'] === 'switch') {
    header('Location: login.php');
} else {
    // Optional: set a logged-out message
    // $_SESSION['success_message'] = "You have been logged out successfully.";
    header('Location: login.php');
}
exit();