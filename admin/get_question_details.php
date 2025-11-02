<?php
require_once('../config.php');
if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

$question_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($question_id === 0) {
    echo json_encode([]);
    exit();
}

function fetch_sub_questions($conn, $question_id, $parent_id) {
    $parts = [];
    $sql = "SELECT * FROM sub_questions WHERE question_id = ? AND " . ($parent_id === null ? "parent_id IS NULL" : "parent_id = ?") . " ORDER BY display_order ASC";
    
    $stmt = $conn->prepare($sql);
    if ($parent_id === null) {
        $stmt->bind_param("i", $question_id);
    } else {
        $stmt->bind_param("ii", $question_id, $parent_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $row['sub_parts'] = fetch_sub_questions($conn, $question_id, $row['id']);
        $parts[] = $row;
    }
    
    $stmt->close();
    return $parts;
}

$question_data = fetch_sub_questions($conn, $question_id, null);

header('Content-Type: application/json');
echo json_encode($question_data);
$conn->close();
?>