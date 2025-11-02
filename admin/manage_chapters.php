<?php
require_once '../config.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit(); }

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_chapter'])) {
        $subject_id = (int)$_POST['subject_id'];
        $name = trim($_POST['name']);
        $chapter_number = (int)$_POST['chapter_number'];
        if ($subject_id > 0 && !empty($name)) {
            $stmt = $conn->prepare("INSERT INTO chapters (subject_id, chapter_number, name) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $subject_id, $chapter_number, $name);
            $stmt->execute();
        }
    }
    if (isset($_POST['update_chapter'])) {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $chapter_number = (int)$_POST['chapter_number'];
        if ($id > 0 && !empty($name)) {
            $stmt = $conn->prepare("UPDATE chapters SET name = ?, chapter_number = ? WHERE id = ?");
            $stmt->bind_param("sii", $name, $chapter_number, $id);
            $stmt->execute();
        }
    }
    if (isset($_POST['delete_chapter'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM chapters WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header("Location: manage_chapters.php" . (isset($_POST['subject_id_filter']) ? "?subject_id=" . $_POST['subject_id_filter'] : ""));
    exit();
}

$filter_subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

// Fetch chapter for editing if ID is provided
$edit_chapter = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT ch.*, s.class_id FROM chapters ch JOIN subjects s ON ch.subject_id = s.id WHERE ch.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_chapter = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Chapters</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2 class="mb-4">Manage Chapters</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0"><?php echo $edit_chapter ? 'Edit Chapter' : 'Add New Chapter'; ?></h5></div>
                <div class="card-body">
                    <form method="POST" action="manage_chapters.php">
                        <?php if ($edit_chapter): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_chapter['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Select Class</label>
                            <select id="class_select" class="form-select" required>
                                <option value="">-- Choose Class --</option>
                                <?php
                                $classes = $conn->query("SELECT * FROM classes ORDER BY name");
                                while($row = $classes->fetch_assoc()) {
                                    $selected = ($edit_chapter && $edit_chapter['class_id'] == $row['id']) ? 'selected' : '';
                                    echo "<option value='{$row['id']}' $selected>".htmlspecialchars($row['name'])."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Subject</label>
                            <select name="subject_id" id="subject_select" class="form-select" required <?php if (!$edit_chapter) echo 'disabled';?>>
                                <option value="">-- First Select a Class --</option>
                            </select>
                        </div>
                         <div class="mb-3">
                            <label class="form-label">Chapter Number (for sorting)</label>
                            <input type="number" name="chapter_number" class="form-control" value="<?php echo htmlspecialchars($edit_chapter['chapter_number'] ?? '0'); ?>" placeholder="e.g., 1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Chapter Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($edit_chapter['name'] ?? ''); ?>" placeholder="e.g., Introduction to Motion" required>
                        </div>
                        <?php if ($edit_chapter): ?>
                            <button type="submit" name="update_chapter" class="btn btn-success w-100"><i class="fas fa-save"></i> Update Chapter</button>
                            <a href="manage_chapters.php" class="btn btn-secondary w-100 mt-2">Cancel Edit</a>
                        <?php else: ?>
                            <button type="submit" name="add_chapter" class="btn btn-primary w-100"><i class="fas fa-plus"></i> Add Chapter</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
             <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Existing Chapters</h5>
                    <form method="GET" class="d-flex" style="width: 250px;">
                        <select name="subject_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="0">-- Show All Subjects --</option>
                            <?php
                            $subjects = $conn->query("SELECT s.id, s.name, c.name as class_name FROM subjects s JOIN classes c ON s.class_id = c.id ORDER BY c.name, s.name");
                            while($row = $subjects->fetch_assoc()) {
                                $selected = ($filter_subject_id == $row['id']) ? 'selected' : '';
                                echo "<option value='{$row['id']}' $selected>".htmlspecialchars($row['class_name'] . ' - ' . $row['name'])."</option>";
                            }
                            ?>
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php
                        $query = "SELECT ch.id, ch.name as chapter_name, ch.chapter_number, s.name as subject_name, c.name as class_name 
                                  FROM chapters ch 
                                  JOIN subjects s ON ch.subject_id = s.id 
                                  JOIN classes c ON s.class_id = c.id";
                        if ($filter_subject_id > 0) {
                            $query .= " WHERE ch.subject_id = ?";
                        }
                        $query .= " ORDER BY c.name, s.name, ch.chapter_number";
                        
                        $stmt = $conn->prepare($query);
                        if ($filter_subject_id > 0) {
                            $stmt->bind_param("i", $filter_subject_id);
                        }
                        $stmt->execute();
                        $result = $stmt->get_result();

                         if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($row['chapter_number'] . ". " . $row['chapter_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($row['class_name'] . ' / ' . $row['subject_name']); ?></small>
                            </div>
                            <div>
                                <a href="manage_chapters.php?edit=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></a>
                                <form method="POST" onsubmit="return confirm('Are you sure? This will delete all its questions.');" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="subject_id_filter" value="<?php echo $filter_subject_id; ?>">
                                    <button type="submit" name="delete_chapter" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </li>
                        <?php endwhile; else: ?>
                             <li class="list-group-item">No chapters found for the selected subject.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('class_select');
    const subjectSelect = document.getElementById('subject_select');
    
    function fetchSubjects(classId, selectedSubjectId = null) {
        subjectSelect.innerHTML = '<option value="">-- Loading... --</option>';
        subjectSelect.disabled = true;

        if (classId) {
            fetch('get_subjects.php?class_id=' + classId)
                .then(response => response.json())
                .then(data => {
                    subjectSelect.innerHTML = '<option value="">-- Choose Subject --</option>';
                    data.forEach(function(subject) {
                        const option = new Option(subject.name, subject.id);
                        if (selectedSubjectId && subject.id == selectedSubjectId) {
                            option.selected = true;
                        }
                        subjectSelect.add(option);
                    });
                    subjectSelect.disabled = false;
                });
        } else {
            subjectSelect.innerHTML = '<option value="">-- First Select a Class --</option>';
        }
    }

    classSelect.addEventListener('change', function() {
        fetchSubjects(this.value);
    });

    // If we are in edit mode, trigger fetch for the selected class
    <?php if ($edit_chapter): ?>
        fetchSubjects('<?php echo $edit_chapter['class_id']; ?>', '<?php echo $edit_chapter['subject_id']; ?>');
    <?php endif; ?>
});
</script>
</body>
</html>