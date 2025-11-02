<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$page_title = 'My Profile';
require_once 'layout/header.php';
?>

<h2 class="text-2xl font-bold mb-5">My Profile</h2>

<div class="space-y-4 bg-gray-800 p-6 rounded-lg">
    <div class="flex justify-between items-center">
        <span class="text-gray-400">Username:</span>
        <span class="font-semibold text-lg"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
    </div>
    <hr class="border-gray-700">
    <div class="flex justify-between items-center">
        <span class="text-gray-400">Class:</span>
        <span class="font-semibold text-lg"><?php echo htmlspecialchars($_SESSION['class_name']); ?></span>
    </div>
</div>

<div class="mt-8 space-y-3">
     <a href="logout.php?action=switch" class="block text-center w-full bg-gray-700 hover:bg-gray-600 transition-all duration-300 p-3 rounded-lg shadow-md">
        <i class="fas fa-random mr-2"></i>Switch Account
    </a>
    <a href="logout.php" class="block text-center w-full bg-red-600 hover:bg-red-700 transition-all duration-300 p-3 rounded-lg shadow-md">
        <i class="fas fa-sign-out-alt mr-2"></i>Logout
    </a>
</div>

<?php require_once 'layout/footer.php'; ?>