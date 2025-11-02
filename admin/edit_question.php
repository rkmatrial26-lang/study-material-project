<?php
require_once '../config.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit(); }

$question_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($question_id === 0) { header('Location: manage_questions.php'); exit(); }

$stmt = $conn->prepare("
    SELECT q.question_title, q.chapter_id, c.subject_id, s.class_id
    FROM questions q
    JOIN chapters c ON q.chapter_id = c.id
    JOIN subjects s ON c.subject_id = s.id
    WHERE q.id = ?
");
$stmt->bind_param("i", $question_id);
$stmt->execute();
$question_info = $stmt->get_result()->fetch_assoc();
if (!$question_info) { header('Location: manage_questions.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Question</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tiny.cloud/1/vxug5f8x43zud9nelx0376l3wzu8bljm41qf710kaomlrnun/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        .question-part { border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 15px; background-color: #f9f9f9; }
        .sub-parts-container { margin-left: 20px; border-left: 2px solid #0d6efd; padding-left: 15px; margin-top:15px; }
        .btn-delete-part { font-size: 1rem; }
        .tox-tinymce { border-radius: 0.375rem; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2 class="mb-4">Edit Question Set</h2>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form id="edit-question-form" action="update_question.php" method="POST">
                <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                <input type="hidden" name="class_id_context" value="<?php echo $question_info['class_id']; ?>">
                <input type="hidden" name="subject_id_context" value="<?php echo $question_info['subject_id']; ?>">
                <input type="hidden" name="chapter_id_context" value="<?php echo $question_info['chapter_id']; ?>">
                
                <div class="mb-3">
                    <label for="question_title" class="form-label fw-bold">Main Question Title (e.g., "Q.1 Answer the following.")</label>
                    <input type="text" id="question_title" name="question_title" class="form-control" required value="<?php echo htmlspecialchars($question_info['question_title']); ?>">
                </div>
                <div>
                    <label class="form-label fw-bold">Question Parts</label>
                    <div id="parts-container">
                        </div>
                    <button type="button" class="btn btn-outline-primary mt-2" onclick="addPart('parts-container', 'parts')"><i class="fas fa-plus"></i> Add Question Part</button>
                </div>
                <div class="text-end mt-4">
                    <a href="manage_questions.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let editorCounter = 0;

function initializeEditor(id) {
    tinymce.init({
        selector: '#' + id,
        plugins: 'lists link image table code help wordcount',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | table | image',
        height: 250,
        menubar: false,
        images_upload_url: 'upload_image.php',
        // images_upload_base_path: '/images/', <-- THIS IS THE BAD LINE. IT HAS BEEN REMOVED.
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
    });
}

function addPart(containerId, namePrefix, data = {}) {
    const container = document.getElementById(containerId);
    const index = Date.now();
    const newPartId = `part-${index}`;
    const questionEditorId = `question-editor-${editorCounter++}`;
    const answerEditorId = `answer-editor-${editorCounter++}`;

    const partHtml = `
        <div class="question-part" id="${newPartId}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label fw-bold text-primary">Question / Answer Part</label>
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-part" onclick="tinymce.remove('#${questionEditorId}'); tinymce.remove('#${answerEditorId}'); document.getElementById('${newPartId}').remove();"><i class="fas fa-trash"></i></button>
            </div>
            <div class="mb-3">
                <label class="form-label">Sub-Question (e.g., "a. What is gravity?")</label>
                <textarea name="${namePrefix}[${index}][question]" id="${questionEditorId}">${data.question_content || ''}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Sub-Answer</label>
                <textarea name="${namePrefix}[${index}][answer]" id="${answerEditorId}">${data.answer_content || ''}</textarea>
            </div>
            <div class="sub-parts-container" id="sub-parts-${newPartId}"></div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addPart('sub-parts-${newPartId}', '${namePrefix}[${index}][sub_parts]')"><i class="fas fa-plus"></i> Add Nested Sub-Part</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', partHtml);
    initializeEditor(questionEditorId);
    initializeEditor(answerEditorId);
    
    // Recursively add sub-parts if they exist in the data
    if (data.sub_parts && data.sub_parts.length > 0) {
        const subContainerId = `sub-parts-${newPartId}`;
        const subNamePrefix = `${namePrefix}[${index}][sub_parts]`;
        data.sub_parts.forEach(subPartData => {
            addPart(subContainerId, subNamePrefix, subPartData);
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Fetch and render the existing question parts
    fetch(`get_question_details.php?id=<?php echo $question_id; ?>`)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                data.forEach(partData => {
                    addPart('parts-container', 'parts', partData);
                });
            }
        })
        .catch(error => console.error('Error fetching question details:', error));

    // Before submitting, destroy TinyMCE instances to ensure textarea content is updated
    document.getElementById('edit-question-form').addEventListener('submit', function() {
        tinymce.triggerSave();
    });
});
</script>
</body>
</html>