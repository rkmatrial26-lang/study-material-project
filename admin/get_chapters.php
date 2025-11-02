<?php
require_once('../config.php');

$chapters = [];
if (isset($_GET['subject_id'])) {
    $subject_id = (int)$_GET['subject_id'];
    
    $stmt = $conn->prepare("SELECT id, name FROM chapters WHERE subject_id = ? ORDER BY chapter_number, name");
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $chapters[] = $row;
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($chapters);
$conn->close();