<?php
require_once '../config.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit(); }

$selected_class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$selected_subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$selected_chapter_id = isset($_GET['chapter_id']) ? (int)$_GET['chapter_id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Questions</title>
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
    <h2 class="mb-4">Manage Questions</h2>

    <div class="card shadow-sm mb-4">
        <div class="card-header"><h5 class="mb-0">Select Context</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">1. Select Class</label>
                    <select id="class_select" class="form-select" onchange="window.location.href='manage_questions.php?class_id='+this.value">
                        <option value="">-- Choose Class --</option>
                         <?php
                            $classes = $conn->query("SELECT * FROM classes ORDER BY name");
                            while($class = $classes->fetch_assoc()) {
                                $selected = ($selected_class_id == $class['id']) ? 'selected' : '';
                                echo "<option value='{$class['id']}' $selected>".htmlspecialchars($class['name'])."</option>";
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">2. Select Subject</label>
                    <select id="subject_select" class="form-select" onchange="window.location.href='manage_questions.php?class_id=<?php echo $selected_class_id; ?>&subject_id='+this.value" <?php if($selected_class_id == 0) echo 'disabled'; ?>>
                         <option value="">-- Choose Subject --</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">3. Select Chapter</label>
                    <select id="chapter_select" class="form-select" onchange="window.location.href='manage_questions.php?class_id=<?php echo $selected_class_id; ?>&subject_id=<?php echo $selected_subject_id; ?>&chapter_id='+this.value" <?php if($selected_subject_id == 0) echo 'disabled'; ?>>
                        <option value="">-- Choose Chapter --</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($selected_chapter_id > 0): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header"><h5 class="mb-0">Add New Question Set</h5></div>
        <div class="card-body">
            <form id="add-question-form" action="add_question.php" method="POST">
                <input type="hidden" name="class_id_context" value="<?php echo $selected_class_id; ?>">
                <input type="hidden" name="subject_id_context" value="<?php echo $selected_subject_id; ?>">
                <input type="hidden" name="chapter_id" value="<?php echo $selected_chapter_id; ?>">
                <div class="col-12 mb-3">
                    <label for="question_title" class="form-label fw-bold">Main Question Title (e.g., "Q.1 Answer the following.")</label>
                    <input type="text" id="question_title" name="question_title" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Question Parts</label>
                    <div id="parts-container"></div>
                    <button type="button" class="btn btn-outline-primary mt-2" onclick="addPart('parts-container', 'parts')"><i class="fas fa-plus"></i> Add Question Part</button>
                </div>
                <div class="col-12 text-end mt-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Question Set</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mt-5">
        <div class="card-header"><h5 class="mb-0">Existing Questions in this Chapter</h5></div>
        <div class="card-body">
            <ul class="list-group">
                <?php
                $stmt = $conn->prepare("SELECT id, question_title FROM questions WHERE chapter_id = ? ORDER BY id");
                $stmt->bind_param("i", $selected_chapter_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?php echo htmlspecialchars($row['question_title']); ?>
                    <div>
                        <a href="edit_question.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i> Edit</a>
                        <form method="POST" action="delete_question.php" onsubmit="return confirm('Are you sure you want to delete this entire question set?');" class="d-inline">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                            <input type="hidden" name="subject_id" value="<?php echo $selected_subject_id; ?>">
                            <input type="hidden" name="chapter_id" value="<?php echo $selected_chapter_id; ?>">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
                        </form>
                    </div>
                </li>
                <?php endwhile; else: ?>
                <li class="list-group-item">No questions found for this chapter. Add one above!</li>
                <?php endif; $stmt->close(); ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>
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
        convert_urls: true
    });
}

function addPart(containerId, namePrefix) {
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
                <textarea name="${namePrefix}[${index}][question]" id="${questionEditorId}"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Sub-Answer</label>
                <textarea name="${namePrefix}[${index}][answer]" id="${answerEditorId}"></textarea>
            </div>
            <div class="sub-parts-container" id="sub-parts-${newPartId}"></div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addPart('sub-parts-${newPartId}', '${namePrefix}[${index}][sub_parts]')"><i class="fas fa-plus"></i> Add Nested Sub-Part</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', partHtml);
    initializeEditor(questionEditorId);
    initializeEditor(answerEditorId);
}

document.addEventListener('DOMContentLoaded', function() {
    const subjectSelect = document.getElementById('subject_select');
    const chapterSelect = document.getElementById('chapter_select');
    
    const selectedClassId = <?php echo $selected_class_id; ?>;
    const selectedSubjectId = <?php echo $selected_subject_id; ?>;
    const selectedChapterId = <?php echo $selected_chapter_id; ?>;

    function fetchSubjects(classId) {
        if (!classId) return;
        fetch(`get_subjects.php?class_id=${classId}`).then(res => res.json()).then(data => {
            subjectSelect.innerHTML = '<option value="">-- Choose Subject --</option>';
            data.forEach(s => {
                const isSelected = s.id == selectedSubjectId;
                subjectSelect.add(new Option(s.name, s.id, false, isSelected));
            });
            if (selectedSubjectId > 0) fetchChapters(selectedSubjectId);
        });
    }

    function fetchChapters(subjectId) {
        if (!subjectId) return;
        fetch(`get_chapters.php?subject_id=${subjectId}`).then(res => res.json()).then(data => {
            chapterSelect.innerHTML = '<option value="">-- Choose Chapter --</option>';
            data.forEach(c => {
                const isSelected = c.id == selectedChapterId;
                chapterSelect.add(new Option(c.name, c.id, false, isSelected));
            });
        });
    }
    
    if (selectedClassId > 0) fetchSubjects(selectedClassId);

    // Before submitting, destroy TinyMCE instances to ensure textarea content is updated
    document.getElementById('add-question-form').addEventListener('submit', function() {
        tinymce.triggerSave();
    });
});
</script>
</body>
</html>