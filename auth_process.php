<?php
require_once 'config.php';
require_once 'mail_config.php'; // <-- ADD THIS NEW LINE

// --- REGISTRATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $class_id = (int)$_POST['class_id'];
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password) || $class_id <= 0) {
        $_SESSION['error_message'] = "Please fill all fields.";
        header('Location: register.php');
        exit();
    }
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error_message'] = "An account with this email already exists.";
        header('Location: register.php');
        exit();
    }
    $stmt->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, class_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $email, $hashed_password, $class_id);
    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['username'] = $username;
        $_SESSION['class_id'] = $class_id;

        $stmt_class = $conn->prepare("SELECT name FROM classes WHERE id = ?");
        $stmt_class->bind_param("i", $class_id);
        $stmt_class->execute();
        $_SESSION['class_name'] = $stmt_class->get_result()->fetch_assoc()['name'];
        $stmt_class->close();
        
        header('Location: subjects.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Registration failed. Please try again.";
        header('Location: register.php');
        exit();
    }
}

// --- LOGIN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Please fill all fields.";
        header('Location: login.php');
        exit();
    }

    $stmt = $conn->prepare("SELECT u.id, u.username, u.password, u.class_id, c.name as class_name FROM users u JOIN classes c ON u.class_id = c.id WHERE u.email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['class_id'] = $user['class_id'];
            $_SESSION['class_name'] = $user['class_name'];
            header('Location: subjects.php');
            exit();
        }
    }
    
    $_SESSION['error_message'] = 'Invalid email or password.';
    header('Location: login.php');
    exit();
}

// --- FORGOT PASSWORD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'forgot_password') {
    $email = trim($_POST['email']);
    if (empty($email)) {
        $_SESSION['error_message'] = "Please enter your email address.";
        header('Location: forgot_password.php');
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $stmt_update = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        $stmt_update->bind_param("sss", $token, $expiry, $email);
        $stmt_update->execute();

        // Send email using the new PHPMailer function
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
        send_password_reset_email($email, $reset_link);
    }
    
    $_SESSION['success_message'] = "If an account with that email exists, a password reset link has been sent.";
    header('Location: forgot_password.php');
    exit();
}


// --- RESET PASSWORD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_password') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (empty($token) || empty($password) || empty($password_confirm)) {
        $_SESSION['error_message'] = "Please fill all fields.";
        header('Location: reset_password.php?token=' . $token);
        exit();
    }

    if ($password !== $password_confirm) {
        $_SESSION['error_message'] = "Passwords do not match.";
        header('Location: reset_password.php?token=' . $token);
        exit();
    }

    $current_time = date("Y-m-d H:i:s");
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > ?");
    $stmt->bind_param("ss", $token, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt_update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $stmt_update->bind_param("si", $hashed_password, $user_id);
        $stmt_update->execute();

        $_SESSION['success_message'] = "Your password has been reset successfully. Please login.";
        header('Location: login.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Invalid or expired token. Please try again.";
        header('Location: forgot_password.php');
        exit();
    }
}

// Redirect back if accessed directly
header('Location: index.php');
exit();