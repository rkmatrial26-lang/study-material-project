<?php
// --- CONFIGURATION ---
$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; // Default XAMPP password is empty
$db_name = 'study_db';
$admin_user = 'rushi';
$admin_pass = 'Rushi@2601'; // Change this if you want

// --- INSTALLATION SCRIPT ---
header('Content-Type: text/plain');

try {
    // 1. CONNECT TO MYSQL and CREATE DATABASE
    $conn = new mysqli($db_host, $db_user, $db_pass);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->query("CREATE DATABASE IF NOT EXISTS $db_name");
    $conn->select_db($db_name);
    echo "✅ Database '$db_name' created or already exists.\n";

    // 2. CREATE TABLES
    $tables = [
        "admin" => "CREATE TABLE IF NOT EXISTS `admin` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL
        )",
        "subjects" => "CREATE TABLE IF NOT EXISTS `subjects` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL
        )",
        "chapters" => "CREATE TABLE IF NOT EXISTS `chapters` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `subject_id` INT NOT NULL,
            `name` VARCHAR(100) NOT NULL,
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
        )",
        "questions" => "CREATE TABLE IF NOT EXISTS `questions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `chapter_id` INT NOT NULL,
            `question` TEXT NOT NULL,
            `answer` TEXT NOT NULL,
            FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
        )"
    ];

    foreach ($tables as $name => $sql) {
        if ($conn->query($sql) === TRUE) {
            echo "✅ Table '$name' created successfully.\n";
        } else {
            die("Error creating table '$name': " . $conn->error);
        }
    }

    // 3. INSERT DEFAULT ADMIN
    $hashed_password = password_hash($admin_pass, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO `admin` (username, password) VALUES (?, ?) ON DUPLICATE KEY UPDATE password = ?");
    $stmt->bind_param("sss", $admin_user, $hashed_password, $hashed_password);
    if ($stmt->execute()) {
        echo "✅ Default admin user created/updated.\n";
        echo "   Username: $admin_user\n";
        echo "   Password: $admin_pass\n";
    }
    $stmt->close();
    $conn->close();

    echo "\n🎉 INSTALLATION COMPLETE! 🎉\n";
    echo "You can now delete this 'install.php' file.\n";
    echo "Go to the admin panel to login: http://localhost/study_material/admin/";

} catch (Exception $e) {
    die("An error occurred: " . $e->getMessage());
}
?>