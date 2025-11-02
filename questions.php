<?php
require_once 'config.php';
$chapter_id = isset($_GET['chapter_id']) ? (int)$_GET['chapter_id'] : 0;
if ($chapter_id === 0) { header('Location: index.php'); exit(); }

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get Chapter and Subject info
$stmt = $conn->prepare("SELECT c.name as chapter_name, s.id as subject_id FROM chapters c JOIN subjects s ON c.subject_id = s.id WHERE c.id = ?");
$stmt->bind_param("i", $chapter_id);
$stmt->execute();
$info = $stmt->get_result()->fetch_assoc();
$stmt->close();

// --- NEW: Fetch ALL sub-questions for this chapter at once ---
$all_sub_questions = [];
$stmt_all = $conn->prepare("
    SELECT sq.*, q.question_title 
    FROM sub_questions sq
    JOIN questions q ON sq.question_id = q.id
    WHERE q.chapter_id = ?
    ORDER BY q.id, sq.display_order ASC
");
$stmt_all->bind_param("i", $chapter_id);
$stmt_all->execute();
$result_all = $stmt_all->get_result();
while ($row = $result_all->fetch_assoc()) {
    $all_sub_questions[] = $row;
}
$stmt_all->close();

// --- NEW: Group sub-questions by their main question ID ---
$grouped_questions = [];
foreach ($all_sub_questions as $sq) {
    $main_question_id = $sq['question_id'];
    if (!isset($grouped_questions[$main_question_id])) {
        $grouped_questions[$main_question_id] = [
            'title' => $sq['question_title'],
            'parts' => []
        ];
    }
    $grouped_questions[$main_question_id]['parts'][] = $sq;
}
$total_questions = count($grouped_questions); // Total is now based on groups, not parts
// --- END NEW ---

$page_title = 'Questions';
require_once 'layout/header.php';

// This function renders the HTML from the database
function display_content($html_content) {
    // We only check if the *trimmed* content is empty.
    // This correctly allows content that *only* has an <img> tag.
    if (empty(trim($html_content))) {
        return;
    }
    
    $html_content = preg_replace('/<table(.*?)>/', '<div class="table-container"><table$1>', $html_content);
    $html_content = str_replace('</table>', '</table></div>', $html_content);
    echo '<div class="content-wrapper">' . htmlspecialchars_decode($html_content) . '</div>';
}
?>

<div class="flex items-center mb-5" id="question-content-top">
    <a href="chapters.php?subject_id=<?php echo $info['subject_id']; ?>" class="text-gray-400 mr-4" id="back-to-chapters-link"><i class="fas fa-arrow-left"></i></a>
    <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($info['chapter_name']); ?></h2>
</div>

<div id="qa-slider-container">
    <?php if ($total_questions > 0): ?>
        <?php 
        $slide_index = 0;
        foreach ($grouped_questions as $group):
        ?>
            <div class="qa-slide" data-index="<?php echo $slide_index; ?>" style="display: <?php echo $slide_index == 0 ? 'block' : 'none'; ?>;">
                
                <div class="main-question-title">
                    <h3><?php echo htmlspecialchars($group['title']); ?></h3>
                </div>

                <?php // --- Loop for all parts in this group --- ?>
                <?php foreach ($group['parts'] as $sq): ?>
                    <div class="sub-part-wrapper" style="margin-bottom: 1.25rem;">
                        
                        <?php 
                        // --- START: NEW FIX ---
                        // Get trimmed content to check if they are empty
                        $question_content = trim($sq['question_content']);
                        $answer_content = trim($sq['answer_content']);
                        
                        // Only show the blue question box if there is content
                        if (!empty($question_content)):
                        ?>
                        <p class="qa-label">Question</p>
                        
                        <div class="qa-part question-part">
                            <?php display_content($question_content); ?>
                        </div>
                        <?php 
                        endif; 
                        // --- END: NEW FIX ---
                        ?>

                        <?php 
                        // --- START: NEW FIX ---
                        // Only show the "Show Answer" button and answer box
                        // if there is actually an answer.
                        if (!empty($answer_content)):
                        ?>
                        <button class="btn-show-answer"><i class="fas fa-eye mr-2"></i>Show Answer</button>

                        <div class="qa-part answer-part" style="display: none;">
                            <p class="qa-label answer-label">Answer</p>
                            
                            <?php display_content($answer_content); ?>
                        </div>
                        <?php 
                        endif;
                        // --- END: NEW FIX ---
                        ?>
                    </div>
                <?php endforeach; ?>
                <?php // --- END loop for parts --- ?>

            </div>
        <?php 
            $slide_index++;
        endforeach; 
        ?>
    <?php else: ?>
        <div class="text-center text-gray-500 mt-10">
            <p>No questions found for this chapter yet.</p>
        </div>
    <?php endif; ?>
</div>


<div class="progress-container mb-6">
    <div class="flex justify-between items-center mb-1">
        <p class="text-gray-400 text-sm font-semibold">Progress</p>
        <p id="progress-counter" class="text-gray-400 text-sm font-semibold">
            <?php if ($total_questions > 0): ?>
                Question 1 / <?php echo $total_questions; ?>
            <?php else: ?>
                No Questions
            <?php endif; ?>
        </p>
    </div>
    <div id="progress-bar-container" class="bg-gray-700 rounded-full h-2 w-full">
        <div id="progress-bar" class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $total_questions > 0 ? (1 / $total_questions * 100) : 0; ?>%;"></div>
    </div>
</div>
<?php if ($total_questions > 0): ?>
<div class="qa-nav-buttons">
    <button id="btn-prev" class="btn-nav" style="display: none;">
        <i class="fas fa-arrow-left mr-2"></i> Previous
    </button>
    <button id="btn-next" class="btn-nav btn-nav-next">
        <?php echo $total_questions > 1 ? 'Next <i class="fas fa-arrow-right ml-2"></i>' : 'Done <i class="fas fa-check ml-2"></i>'; ?>
    </button>
</div>
<?php endif; ?>
<?php require_once 'layout/footer.php'; ?>