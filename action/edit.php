<?php
session_start();
require dirname(__DIR__) . '/config.php';
$conn = getConnection();

// Ensure user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Check task ID
$task_id = (int)($_GET['id'] ?? 0);
if (!$task_id) {
    $_SESSION['error'] = "No task ID provided.";
    header("Location: ../task.php");
    exit;
}

// Fetch current task
$stmt = $conn->prepare("SELECT title, deskripsi, prioritas, status FROM task WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();
$stmt->close();

if (!$task) {
    $_SESSION['error'] = "Task not found.";
    header("Location: ../task.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $prioritas = (int)($_POST['prioritas'] ?? 0);
    $status = (int)($_POST['status'] ?? 0); // 0 = pending, 1 = completed

    $stmt = $conn->prepare("UPDATE task SET title=?, deskripsi=?, prioritas=?, status=? WHERE id=? AND user_id=?");
    $stmt->bind_param("ssiiii", $title, $deskripsi, $prioritas, $status, $task_id, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Task updated successfully!";
        header("Location: ../task.php?id=$task_id"); // Redirect to same page to show message
        exit;
    } else {
        $_SESSION['error'] = "Database error: " . $conn->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Task</title>
<style>
body { font-family: sans-serif; background: #f7f7f7; padding: 40px; }
.container { max-width: 500px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
label { display: block; margin-top: 10px; font-weight: bold; }
input, textarea, select { width: 100%; padding: 8px; margin-top: 5px; border-radius: 6px; border: 1px solid #ccc; }
button { background: #007bff; color: white; border: none; padding: 10px 15px; border-radius: 6px; margin-top: 15px; cursor: pointer; }
button:hover { background: #0056b3; }
.alert { padding: 10px; border-radius: 6px; margin-bottom: 15px; }
.alert-success { background: #c8f7c5; color: #155724; }
.alert-error { background: #f8d7da; color: #721c24; }
.btn-cancel { background: #dc3545; text-decoration: none; color: white; padding: 10px 15px; border-radius: 6px; margin-left: 10px; }
</style>
</head>
<body>
<div class="container">
<h2>Edit Task</h2>

<?php if(!empty($_SESSION['error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php if(!empty($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
<?php endif; ?>

<form method="POST">
    <label>Title</label>
    <input type="text" name="title" value="<?= htmlspecialchars($task['title']); ?>" required>

    <label>Description</label>
    <textarea name="deskripsi"><?= htmlspecialchars($task['deskripsi']); ?></textarea>

    <label>Priority</label>
    <select name="prioritas">
        <option value="0" <?= $task['prioritas']==0?'selected':'' ?>>Not Prioritized</option>
        <option value="1" <?= $task['prioritas']==1?'selected':'' ?>>Prioritized</option>
    </select>

    <label>Status</label>
    <select name="status">
        <option value="0" <?= $task['status']==0?'selected':'' ?>>Pending</option>
        <option value="1" <?= $task['status']==1?'selected':'' ?>>Completed</option>
    </select>

    <button type="submit">Save Changes</button>
    <a href="../task.php" class="btn-cancel">Cancel</a>
</form>
</div>
</body>
</html>
