<?php
require_once('../config.php');

$subjects = [];
if (isset($_GET['class_id'])) {
    $class_id = (int)$_GET['class_id'];
    
    $stmt = $conn->prepare("SELECT id, name FROM subjects WHERE class_id = ? ORDER BY name");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($subjects);
$conn->close();
?>