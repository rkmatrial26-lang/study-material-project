<?php
require_once '../config.php';
require_once 'cloudinary_config.php'; // For Cloudinary uploads
use Cloudinary\Api\Upload\UploadApi;

if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit(); }

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadApi = new UploadApi();

    // ADD SUBJECT
    if (isset($_POST['add_subject'])) {
        $name = trim($_POST['name']);
        $class_id = (int)$_POST['class_id'];
        $thumbnail_url = null;

        // Handle file upload
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
            try {
                $upload_result = $uploadApi->upload($_FILES['thumbnail']['tmp_name'], ["folder" => "subject_thumbnails"]);
                $thumbnail_url = $upload_result['secure_url'];
            } catch (Exception $e) { /* Handle error if needed */ }
        }

        if (!empty($name) && $class_id > 0) {
            $stmt = $conn->prepare("INSERT INTO subjects (class_id, name, thumbnail_url) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $class_id, $name, $thumbnail_url);
            $stmt->execute();
        }
    }
    // UPDATE SUBJECT
    if (isset($_POST['update_subject'])) {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $class_id = (int)$_POST['class_id'];
        $thumbnail_url = $_POST['existing_thumbnail_url']; // Keep existing URL by default

        // Handle new file upload
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
            try {
                $upload_result = $uploadApi->upload($_FILES['thumbnail']['tmp_name'], ["folder" => "subject_thumbnails"]);
                $thumbnail_url = $upload_result['secure_url'];
            } catch (Exception $e) { /* Handle error if needed */ }
        }

        if (!empty($name) && $id > 0 && $class_id > 0) {
            $stmt = $conn->prepare("UPDATE subjects SET name = ?, class_id = ?, thumbnail_url = ? WHERE id = ?");
            $stmt->bind_param("sisi", $name, $class_id, $thumbnail_url, $id);
            $stmt->execute();
        }
    }
    // DELETE SUBJECT
    if (isset($_POST['delete_subject'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header("Location: manage_subjects.php");
    exit();
}

// Fetch subject for editing
$edit_subject = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_subject = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Subjects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2 class="mb-4">Manage Subjects</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0"><?php echo $edit_subject ? 'Edit Subject' : 'Add New Subject'; ?></h5></div>
                <div class="card-body">
                    <form method="POST" action="manage_subjects.php" enctype="multipart/form-data">
                        <?php if ($edit_subject): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_subject['id']; ?>">
                            <input type="hidden" name="existing_thumbnail_url" value="<?php echo htmlspecialchars($edit_subject['thumbnail_url'] ?? ''); ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Select Class</label>
                            <select name="class_id" class="form-select" required>
                                <option value="">-- Choose Class --</option>
                                <?php
                                $classes = $conn->query("SELECT * FROM classes ORDER BY name");
                                while ($row = $classes->fetch_assoc()) {
                                    $selected = ($edit_subject && $edit_subject['class_id'] == $row['id']) ? 'selected' : '';
                                    echo "<option value='{$row['id']}' $selected>" . htmlspecialchars($row['name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($edit_subject['name'] ?? ''); ?>" placeholder="e.g., Physics" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject Thumbnail (Optional)</label>
                            <?php if ($edit_subject && !empty($edit_subject['thumbnail_url'])): ?>
                                <img src="<?php echo htmlspecialchars($edit_subject['thumbnail_url']); ?>" alt="Current Thumbnail" class="img-thumbnail mb-2" style="max-width: 100px;">
                                <p class="form-text">Current image. Upload a new one to replace it.</p>
                            <?php endif; ?>
                            <input type="file" name="thumbnail" class="form-control" accept="image/*">
                        </div>
                        <?php if ($edit_subject): ?>
                            <button type="submit" name="update_subject" class="btn btn-success w-100"><i class="fas fa-save"></i> Update Subject</button>
                            <a href="manage_subjects.php" class="btn btn-secondary w-100 mt-2">Cancel Edit</a>
                        <?php else: ?>
                            <button type="submit" name="add_subject" class="btn btn-primary w-100"><i class="fas fa-plus"></i> Add Subject</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0">Existing Subjects</h5></div>
                <div class="card-body">
                    <?php
                    $classes_with_subjects = $conn->query("SELECT * FROM classes ORDER BY name");
                    while($class = $classes_with_subjects->fetch_assoc()):
                        $stmt = $conn->prepare("SELECT * FROM subjects WHERE class_id = ? ORDER BY name");
                        $stmt->bind_param("i", $class['id']);
                        $stmt->execute();
                        $subjects_result = $stmt->get_result();
                        if ($subjects_result->num_rows > 0):
                    ?>
                    <h6 class="mt-3 mb-2 ps-2 border-start border-4 border-primary"><?php echo htmlspecialchars($class['name']); ?></h6>
                    <ul class="list-group mb-4">
                        <?php while ($subject = $subjects_result->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                           <div>
                                <?php if (!empty($subject['thumbnail_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($subject['thumbnail_url']); ?>" alt="Thumb" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                <?php endif; ?>
                                <?php echo htmlspecialchars($subject['name']); ?>
                           </div>
                            <div>
                                <a href="manage_subjects.php?edit=<?php echo $subject['id']; ?>" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></a>
                                <form method="POST" onsubmit="return confirm('Are you sure? This will delete all its chapters and questions.');" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo $subject['id']; ?>">
                                    <button type="submit" name="delete_subject" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                    <?php endif; endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>