<?php
require_once 'config.php';
$page_title = 'Login';

if (isset($_SESSION['user_id'])) {
    header('Location: subjects.php');
    exit();
}

require_once 'layout/header.php';
?>

<h2 class="text-2xl font-bold mb-5 text-center">Welcome Back!</h2>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">
        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-500 text-white p-3 rounded-lg mb-4 text-center">
        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
    </div>
<?php endif; ?>

<form action="auth_process.php" method="POST" class="space-y-4">
    <input type="hidden" name="action" value="login">
    <div>
        <label for="email" class="block mb-2 text-sm font-medium text-gray-300">Email</label>
        <input type="email" name="email" id="email" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="name@company.com" required>
    </div>
    <div>
        <div class="flex justify-between items-center">
            <label for="password" class="block mb-2 text-sm font-medium text-gray-300">Password</label>
            <a href="forgot_password.php" class="text-sm text-blue-500 hover:underline">Forgot?</a>
        </div>
        <input type="password" name="password" id="password" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="••••••••" required>
    </div>
    <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Login to your account</button>
    <div class="text-sm font-medium text-gray-400 text-center">
        Not registered? <a href="register.php" class="text-blue-500 hover:underline">Create account</a>
    </div>
</form>

<?php require_once 'layout/footer.php'; ?>