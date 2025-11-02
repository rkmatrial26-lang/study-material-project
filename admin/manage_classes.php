<?php
require_once '../config.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit(); }

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new class
    if (isset($_POST['add_class'])) {
        $name = trim($_POST['name']);
        if (!empty($name)) {
            $stmt = $conn->prepare("INSERT INTO classes (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
        }
    }
    // Update existing class
    if (isset($_POST['update_class'])) {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        if (!empty($name) && $id > 0) {
            $stmt = $conn->prepare("UPDATE classes SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $id);
            $stmt->execute();
        }
    }
    // Delete class
    if (isset($_POST['delete_class'])) {
        $id = (int)$_POST['id'];
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM classes WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
    }
    header("Location: manage_classes.php"); // Prevent form resubmission
    exit();
}

// Fetch class for editing if ID is provided
$edit_class = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_class = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Classes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<?php include 'navbar.php'; // Using a separate navbar file for consistency ?>
<div class="container mt-4">
    <h2 class="mb-4">Manage Classes (e.g., 10th STD)</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0"><?php echo $edit_class ? 'Edit Class' : 'Add New Class'; ?></h5></div>
                <div class="card-body">
                    <form method="POST" action="manage_classes.php">
                        <?php if ($edit_class): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_class['id']; ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Class Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($edit_class['name'] ?? ''); ?>" placeholder="e.g., 10th STD" required>
                        </div>
                        <?php if ($edit_class): ?>
                            <button type="submit" name="update_class" class="btn btn-success w-100"><i class="fas fa-save"></i> Update Class</button>
                            <a href="manage_classes.php" class="btn btn-secondary w-100 mt-2">Cancel Edit</a>
                        <?php else: ?>
                            <button type="submit" name="add_class" class="btn btn-primary w-100"><i class="fas fa-plus"></i> Add Class</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0">Existing Classes</h5></div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php
                        // FIX: Order classes by length first, then by name, for natural sorting
                        $result = $conn->query("SELECT * FROM classes ORDER BY LENGTH(name), name");
                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($row['name']); ?>
                            <div>
                                <a href="manage_classes.php?edit=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></a>
                                <form method="POST" onsubmit="return confirm('WARNING: Deleting this class will also delete ALL its subjects, chapters, and questions. Are you sure?');" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_class" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </li>
                        <?php endwhile; else: ?>
                            <li class="list-group-item">No classes found. Add one to get started!</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>