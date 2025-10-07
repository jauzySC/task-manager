<?php
session_start();
require dirname(__DIR__) . '/config.php'; 
$conn = getConnection(); 

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $prioritas = (int)($_POST['prioritas'] ?? 0);
    $status = trim($_POST['status'] ?? 'pending');
    $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null; // new
    $created_at = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("
        INSERT INTO task (user_id, title, deskripsi, prioritas, status, created_at, deadline)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ississs", $user_id, $title, $deskripsi, $prioritas, $status, $created_at, $deadline);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Task added successfully!";
        header("Location: ../task.php");
        exit;
    } else {
        $_SESSION['error'] = "Database error: ".$conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Task</title>
<style>
body { font-family: Arial, sans-serif; background:#f7f7f7; padding:40px;}
.container { max-width:500px; background:white; margin:auto; padding:25px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.1);}
label { display:block; margin-top:10px; font-weight:bold; }
input, textarea, select { width:100%; padding:8px; margin-top:5px; border-radius:6px; border:1px solid #ccc; font-size:14px; }
button { background:#007bff; color:white; border:none; padding:10px 15px; border-radius:6px; margin-top:15px; cursor:pointer; }
button:hover { background:#0056b3; }
.btn-cancel { background:#dc3545; text-decoration:none; color:white; padding:10px 15px; border-radius:6px; margin-left:10px;}
</style>
</head>
<body>
<div class="container">
<h2>Add New Task</h2>

<?php if(!empty($_SESSION['error'])): ?>
    <div style="color:red;"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>
<?php if(!empty($_SESSION['success'])): ?>
    <div style="color:green;"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
<?php endif; ?>

<form method="POST">
    <label>Title</label>
    <input type="text" name="title" required>

    <label>Description</label>
    <textarea name="deskripsi"></textarea>

    <label>Priority</label>
    <select name="prioritas">
        <option value="0">Not Prioritized</option>
        <option value="1">Prioritized</option>
    </select>

    <label>Status</label>
    <select name="status">
        <option value="pending">Pending</option>
        <option value="completed">Completed</option>
    </select>


    <button type="submit">Add Task</button>
    <a href="../task.php" class="btn-cancel">Cancel</a>
</form>
</div>
</body>
</html>
