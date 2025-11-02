<?php
require_once '../config.php';
if (!isset($_SESSION['admin_id'])) { exit('Unauthorized'); }

function save_question_parts($conn, $question_id, $parts, $parent_id = null) {
    if (empty($parts) || !is_array($parts)) {
        return;
    }

    $order = 0;
    foreach ($parts as $part) {
        $question_content = $part['question'] ?? '';
        $answer_content = $part['answer'] ?? '';

        $stmt = $conn->prepare(
            "INSERT INTO sub_questions (question_id, parent_id, question_content, answer_content, display_order) VALUES (?, ?, ?, ?, ?)"
        );
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $chapter_id = $_POST['chapter_id'];
    $question_title = $_POST['question_title'];
    
    $class_id_context = $_POST['class_id_context'];
    $subject_id_context = $_POST['subject_id_context'];

    $stmt = $conn->prepare("INSERT INTO questions (chapter_id, question_title) VALUES (?, ?)");
    $stmt->bind_param("is", $chapter_id, $question_title);
    $stmt->execute();
    $question_id = $stmt->insert_id;
    $stmt->close();

    if (isset($_POST['parts'])) {
        save_question_parts($conn, $question_id, $_POST['parts']);
    }

    $conn->close();

    $redirect_url = "manage_questions.php?class_id={$class_id_context}&subject_id={$subject_id_context}&chapter_id={$chapter_id}";
    header("Location: " . $redirect_url);
    exit();
}

header("Location: manage_questions.php");
exit();