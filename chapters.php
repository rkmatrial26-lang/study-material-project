<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
if ($subject_id === 0) { 
    header('Location: subjects.php'); 
    exit(); 
}

$stmt_info = $conn->prepare("SELECT name FROM subjects WHERE id = ?");
$stmt_info->bind_param("i", $subject_id);
$stmt_info->execute();
$subject_info = $stmt_info->get_result()->fetch_assoc();
$page_title = 'Chapters';

require_once 'layout/header.php';
?>

<div class="flex items-center mb-5">
    <a href="subjects.php" class="text-gray-400 mr-4"><i class="fas fa-arrow-left"></i></a>
    <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($subject_info['name']); ?></h2>
</div>

<div class="chapter-list-container">
    <?php
    // --- UPDATED SQL QUERY ---
    // This new query gets the count of questions for each chapter
    $stmt = $conn->prepare("
        SELECT c.*, (SELECT COUNT(*) FROM questions WHERE chapter_id = c.id) as question_count
        FROM chapters c
        WHERE c.subject_id = ? 
        ORDER BY c.chapter_number, c.name
    ");
    // --- END UPDATED SQL ---
    
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0):
        $animation_delay = 0; // For stagger animation
        while ($row = $result->fetch_assoc()):
            $animation_delay += 100; // Add 100ms delay for each item
    ?>
        <a href="questions.php?chapter_id=<?php echo $row['id']; ?>" 
           class="chapter-item" 
           style="animation-delay: <?php echo $animation_delay; ?>ms;">
            
            <div class="chapter-number-box">
                <?php echo htmlspecialchars($row['chapter_number']); ?>
            </div>
            
            <div class="chapter-text-content">
                <div class="chapter-title">
                    <?php echo htmlspecialchars($row['name']); ?>
                </div>
                <div class="chapter-subtitle">
                    <?php echo $row['question_count']; ?> Question Set<?php echo ($row['question_count'] != 1) ? 's' : ''; ?>
                </div>
            </div>
            
            <i class="fas fa-chevron-right chapter-chevron"></i>
        </a>
        <?php 
        endwhile; 
    else: 
    ?>
        <div class="text-center text-gray-500 mt-10">
            <p>No chapters found for this subject yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>