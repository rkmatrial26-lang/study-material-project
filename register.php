<?php
require_once 'config.php';
$page_title = 'Create Account';

// If user is already logged in, redirect them away
if (isset($_SESSION['user_id'])) {
    header('Location: subjects.php');
    exit();
}

require_once 'layout/header.php';
?>

<h2 class="text-2xl font-bold mb-5 text-center">Create an Account</h2>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">
        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>

<form action="auth_process.php" method="POST" class="space-y-4">
    <input type="hidden" name="action" value="register">
    <div>
        <label for="username" class="block mb-2 text-sm font-medium text-gray-300">Username</label>
        <input type="text" name="username" id="username" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Your Name" required>
    </div>
     <div>
        <label for="email" class="block mb-2 text-sm font-medium text-gray-300">Email</label>
        <input type="email" name="email" id="email" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="name@company.com" required>
    </div>
    <div>
        <label for="class_id" class="block mb-2 text-sm font-medium text-gray-300">Select your Class</label>
        <select name="class_id" id="class_id" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
            <option value="" disabled selected>-- Choose your class --</option>
            <?php
            // Query classes and sort them naturally
            $result = $conn->query("SELECT * FROM classes ORDER BY LENGTH(name), name");
            while ($row = $result->fetch_assoc()):
            ?>
                <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div>
        <label for="password" class="block mb-2 text-sm font-medium text-gray-300">Password</label>
        <input type="password" name="password" id="password" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="••••••••" required>
    </div>
    <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Create account</button>
    <div class="text-sm font-medium text-gray-400 text-center">
        Already have an account? <a href="login.php" class="text-blue-500 hover:underline">Login here</a>
    </div>
</form>

<?php require_once 'layout/footer.php'; ?>