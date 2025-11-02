</main>

        <footer class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-gray-800 border-t border-gray-700 z-10">
            <nav class="flex justify-around items-center p-2">
                <a href="index.php" class="flex flex-col items-center w-full py-2 rounded-lg <?php echo is_active(['index.php']); ?> hover:bg-gray-700 transition-colors">
                    <i class="fas fa-home text-xl"></i>
                    <span class="text-xs mt-1">Home</span>
                </a>
                <a href="subjects.php" class="flex flex-col items-center w-full py-2 rounded-lg <?php echo is_active(['subjects.php', 'chapters.php', 'questions.php']); ?> hover:bg-gray-700 transition-colors">
                    <i class="fas fa-book text-xl"></i>
                    <span class="text-xs mt-1">Subjects</span>
                </a>
                <a href="profile.php" class="flex flex-col items-center w-full py-2 rounded-lg <?php echo is_active(['profile.php']); ?> hover:bg-gray-700 transition-colors">
                    <i class="fas fa-user text-xl"></i>
                    <span class="text-xs mt-1">Profile</span>
                </a>
            </nav>
        </footer>
    </div>
    <script src="js/main.js"></script>
    <script src="js/side-menu.js"></script>

    <?php // -- THIS IS THE NEW CODE THAT WAS ADDED -- ?>
    <?php if (basename($_SERVER['PHP_SELF']) == 'questions.php'): ?>
        <script src="js/question-slider.js"></script>
    <?php endif; ?>
    
</body>
</html>