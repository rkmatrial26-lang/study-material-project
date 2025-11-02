<?php
function is_active($page_names) {
    $current_page = basename($_SERVER['PHP_SELF']);
    // Handle the case where the user is on index.php but it's acting as subjects.php
    if (in_array($current_page, ['index.php', 'subjects.php']) && in_array('subjects.php', $page_names)) {
        return 'text-blue-500';
    }
    return in_array($current_page, $page_names) ? 'text-blue-500' : 'text-gray-400';
}

function get_subject_icon($subject_name) {
    $subject_name = strtolower($subject_name);
    if (strpos($subject_name, 'english') !== false) return 'fa-book-open';
    if (strpos($subject_name, 'math') !== false) return 'fa-calculator';
    if (strpos($subject_name, 'science') !== false) return 'fa-flask';
    if (strpos($subject_name, 'history') !== false) return 'fa-landmark';
    if (strpos($subject_name, 'geography') !== false) return 'fa-globe-asia';
    if (strpos($subject_name, 'marathi') !== false) return 'fa-feather-alt';
    return 'fa-book';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ClassmateApp' : 'ClassmateApp'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <?php if (basename($_SERVER['PHP_SELF']) == 'questions.php'): ?>
        <link rel="stylesheet" href="css/questions.css">
    <?php endif; ?>
    <?php if (basename($_SERVER['PHP_SELF']) == 'chapters.php'): ?>
        <link rel="stylesheet" href="css/chapter-list.css">
    <?php endif; ?>
    <link rel="stylesheet" href="css/loader.css">
</head>
<body class="bg-gray-900 text-white font-sans">
    
    <div id="loader-overlay"><div class="loader"></div></div>

    <div id="menu-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>
    <div id="side-menu" class="side-menu fixed top-0 left-0 h-full w-64 bg-gray-900 text-white p-6 z-50 transform -translate-x-full transition-transform duration-300">
        <div class="flex items-center mb-8">
            <img src="images/logo.png" alt="Logo" class="h-10 w-10 mr-3">
            <span class="text-2xl font-bold">ClassmateApp</span>
        </div>
        <nav class="space-y-4">
            <a href="#" id="app-share" class="flex items-center space-x-3 text-lg hover:text-blue-400">
                <i class="fas fa-share-alt w-6"></i><span>App Share</span>
            </a>
            <a href="#" class="flex items-center space-x-3 text-lg hover:text-blue-400">
                <i class="fas fa-star w-6"></i><span>Rate Us</span>
            </a>
            <a href="mailto:youremail@example.com" class="flex items-center space-x-3 text-lg hover:text-blue-400">
                <i class="fas fa-comment-dots w-6"></i><span>Feedback</span>
            </a>
            <a href="privacy.php" class="flex items-center space-x-3 text-lg hover:text-blue-400">
                <i class="fas fa-shield-alt w-6"></i><span>Privacy Policy</span>
            </a>
        </nav>
    </div>

    <div class="container max-w-md mx-auto flex flex-col min-h-screen">
        <header class="sticky top-0 bg-gray-900 bg-opacity-80 backdrop-blur-md z-10 p-4 flex items-center justify-between border-b border-gray-800">
            <button id="menu-button" class="text-xl w-8 h-8 flex items-center justify-center">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="flex items-center space-x-3">
                <img src="images/logo.png" alt="ClassmateApp Logo" class="h-8 w-8 rounded-full">
                <h1 class="text-xl font-bold">ClassmateApp</h1>
            </div>

            <div class="w-auto text-right">
                <?php if (isset($_SESSION['class_name'])): ?>
                    <span class="text-sm font-semibold bg-gray-700 px-2 py-1 rounded"><?php echo htmlspecialchars($_SESSION['class_name']); ?></span>
                <?php endif; ?>
            </div>
        </header>

        <main class="flex-grow p-4 pb-24">