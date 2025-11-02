<?php
require_once '../config.php';
if (!isset($_SESSION['admin_id'])) { exit('Unauthorized'); }

// This is the same function from add_question.php
function save_question_parts($conn, $question_id, $parts, $parent_id = null) {
    if (empty($parts) || !is_array($parts)) {
        return;
    }
    $order = 0;
    foreach ($parts as $part) {
        $question_content = $part['question'] ?? '';
        $answer_content = $part['answer'] ?? '';

        $stmt = $conn->prepare("INSERT INTO sub_questions (question_id, parent_id, question_content, answer_content, display_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissi", $question_id, $parent_id, $question_content, $answer_content, $order);
        $stmt->execute();
        $new_sub_question_id = $stmt->insert_id;
        $stmt->close();

        if (isset($part['sub_parts']) && is_array($part['sub_parts'])) {
            save_question_parts($conn, $question_id, $part['sub_parts'], $new_sub_question_id);
        }
        $order++;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['question_id'])) {
    $question_id = (int)$_POST['question_id'];
    $question_title = $_POST['question_title'];
    
    // Redirect context
    $class_id_context = $_POST['class_id_context'];
    $subject_id_context = $_POST['subject_id_context'];
    $chapter_id_context = $_POST['chapter_id_context'];

    $conn->begin_transaction();
    try {
        // 1. Update the main question title
        $stmt = $conn->prepare("UPDATE questions SET question_title = ? WHERE id = ?");
        $stmt->bind_param("si", $question_title, $question_id);
        $stmt->execute();
        $stmt->close();

        // 2. Delete all existing sub-questions for this question
        $stmt = $conn->prepare("DELETE FROM sub_questions WHERE question_id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $stmt->close();

        // 3. Re-insert all parts from the form (this is the simplest way)
        if (isset($_POST['parts'])) {
            save_question_parts($conn, $question_id, $_POST['parts']);
        }
        
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        // Optionally, handle the error
        die("An error occurred: " . $e->getMessage());
    }

    $conn->close();

    $redirect_url = "manage_questions.php?class_id={$class_id_context}&subject_id={$subject_id_context}&chapter_id={$chapter_id_context}";
    header("Location: " . $redirect_url);
    exit();
}

header("Location: manage_questions.php");
exit();
?>