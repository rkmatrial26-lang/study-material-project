<?php
require_once 'config.php';
// If user is not logged in, redirect them to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$class_id = (int)$_SESSION['class_id'];
$class_name = $_SESSION['class_name'];

$page_title = 'My Subjects';
require_once 'layout/header.php';
?>

<h2 class="text-2xl font-bold mb-5">My Subjects</h2>

<div class="grid grid-cols-2 gap-4">
    <?php
    // Updated query to get chapter count and thumbnail for each subject
    $stmt = $conn->prepare("
        SELECT s.*, (SELECT COUNT(*) FROM chapters WHERE subject_id = s.id) as chapter_count 
        FROM subjects s 
        WHERE s.class_id = ? 
        ORDER BY s.name
    ");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
    ?>
        <a href="chapters.php?subject_id=<?php echo $row['id']; ?>" class="block bg-gray-800 rounded-lg shadow-lg overflow-hidden transition-transform transform hover:-translate-y-1 duration-300">
            <div class="w-full">
                <?php if (!empty($row['thumbnail_url'])): ?>
                    <img src="<?php echo htmlspecialchars($row['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="w-full h-auto">
                <?php else: ?>
                    <div class="w-full bg-blue-600 flex items-center justify-center aspect-[2/3]">
                        <i class="fas <?php echo get_subject_icon($row['name']); ?> text-5xl text-white opacity-75"></i>
                    </div>
                <?php endif; ?>
            </div>
             <div class="p-3">
                <p class="font-bold truncate"><?php echo htmlspecialchars($row['name']); ?></p>
                <p class="text-sm text-gray-400"><?php echo $row['chapter_count']; ?> Chapters</p>
            </div>
        </a>
    <?php 
        endwhile; 
    else: 
    ?>
        <div class="col-span-2 text-center text-gray-500 mt-10">
            <p>No subjects found for this class yet.</p>
            <p class="text-sm">Subjects can be added by an admin.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>