<?php
require_once 'config.php';
$page_title = 'Reset Password';

$token = $_GET['token'] ?? '';
if (empty($token)) {
    header('Location: forgot_password.php');
    exit();
}

// Verify token exists and is not expired
$current_time = date("Y-m-d H:i:s");
$stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > ?");
$stmt->bind_param("ss", $token, $current_time);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "This password reset link is invalid or has expired.";
    header('Location: forgot_password.php');
    exit();
}

require_once 'layout/header.php';
?>

<h2 class="text-2xl font-bold mb-5 text-center">Create a New Password</h2>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">
        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>

<form action="auth_process.php" method="POST" class="space-y-4">
    <input type="hidden" name="action" value="reset_password">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
    
    <div>
        <label for="password" class="block mb-2 text-sm font-medium text-gray-300">New Password</label>
        <input type="password" name="password" id="password" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="••••••••" required>
    </div>
    <div>
        <label for="password_confirm" class="block mb-2 text-sm font-medium text-gray-300">Confirm New Password</label>
        <input type="password" name="password_confirm" id="password_confirm" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="••••••••" required>
    </div>

    <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Reset Password</button>
</form>

<?php require_once 'layout/footer.php'; ?>