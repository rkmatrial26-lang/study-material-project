<?php
require_once('db_config.php');

if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    // ON DELETE CASCADE in the database will handle deleting all related sub_questions.
    $stmt_delete = $conn->prepare("DELETE FROM questions WHERE id = ?");
    $stmt_delete->bind_param("i", $id);
    $stmt_delete->execute();
    $stmt_delete->close();
    $conn->close();
}

// Redirect back to the manage questions page with context
$class_id = $_POST['class_id'] ?? 0;
$subject_id = $_POST['subject_id'] ?? 0;
$chapter_id = $_POST['chapter_id'] ?? 0;
header("Location: manage_questions.php?class_id=$class_id&subject_id=$subject_id&chapter_id=$chapter_id");
exit();