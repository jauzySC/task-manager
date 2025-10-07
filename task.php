<?php
session_start();
require __DIR__.'/config.php';
$conn = getConnection();

$user = $_SESSION['username'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Delete task
if (isset($_GET['delete'])) {
    $task_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM task WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: task.php");
    exit;
}

// Sorting
$sort = in_array($_GET['sort'] ?? '', ['prioritas', 'created_at']) ? $_GET['sort'] : 'created_at';
$order = ($_GET['order'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$toggleOrder = $order === 'ASC' ? 'desc' : 'asc';

// Search
$search = $_GET['search'] ?? '';
$searchSql = "";
if ($search) {
    $searchSql = " AND title LIKE ?";
}

// Fetch tasks
$query = "SELECT id, title, status, created_at, prioritas FROM task WHERE user_id=?" . $searchSql . " ORDER BY $sort $order";
$stmt = $conn->prepare($query);
if ($search) {
    $likeSearch = "%$search%";
    $stmt->bind_param("is", $user_id, $likeSearch);
} else {
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$tasks = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tasks</title>
<style>
body{font-family:sans-serif; max-width:900px;margin:40px auto;background:#f8f9fa;}
table{width:100%;border-collapse:collapse;background:white;}
th,td{padding:10px;border-bottom:1px solid #eee;text-align:left;}
th{background:#f1f1f1;cursor:pointer;}
.done{text-decoration:line-through;color:#999;}
.badge{padding:3px 8px;border-radius:6px;font-size:12px;}
.badge.pending{background:#ffe5b4;}
.badge.done{background:#c8f7c5;}
.actions a{margin-right:5px;text-decoration:none;color:#007bff;}
.actions a:hover{text-decoration:underline;}
.add-btn input{padding:6px 12px;border:none;border-radius:5px;background:#007bff;color:white;cursor:pointer;}
.add-btn input:hover{background:#0056b3;}
.logout{background:red;color:white;padding:8px 14px;border:none;border-radius:5px;cursor:pointer;position:fixed;top:20px;right:30px;}
.logout:hover{background:#d93636;}
nav ul{list-style:none;padding:0;display:flex;gap:20px;margin-bottom:20px;}
nav ul li{display:inline;}
nav ul li a{text-decoration:none;color:#007bff;font-weight:bold;}
nav ul li a:hover{text-decoration:underline;}
.search-form{margin-bottom:20px;}
.search-form input[type="text"]{padding:6px 10px;border-radius:5px;border:1px solid #ccc;}
.search-form input[type="submit"]{padding:6px 10px;border-radius:5px;border:none;background:#007bff;color:white;cursor:pointer;}
.search-form input[type="submit"]:hover{background:#0056b3;}
</style>
</head>
<body>

<h2>Welcome, <?= htmlspecialchars($user) ?></h2>
<form action="logout.php" method="POST"><button class="logout">Logout</button></form>

<!-- Navbar -->
<nav>
    <ul>
      <li><a href="task.php">Daftar Tugas</a></li>
      <li><a href="about.php">Tentang</a></li>
    </ul>
</nav>

<!-- Search Form -->
<form class="search-form" method="GET">
    <input type="text" name="search" placeholder="Search tasks..." value="<?= htmlspecialchars($search) ?>">
    <input type="submit" value="Search">
</form>

<table>
<tr>
    <th>Task</th>
    <th>Status</th>
    <th>Actions</th>
    <th><a href="?sort=prioritas&order=<?= $sort==='prioritas'?$toggleOrder:'desc' ?>&search=<?= urlencode($search) ?>">Priority</a></th>
    <th><a href="?sort=created_at&order=<?= $sort==='created_at'?$toggleOrder:'desc' ?>&search=<?= urlencode($search) ?>">Created At</a></th>
    <th>
        <form class="add-btn" action="action/add.php"><input type="submit" value="Add"></form>
    </th>
</tr>

<?php if($tasks && $tasks->num_rows>0):
    while($t = $tasks->fetch_assoc()):
        $statusClass = $t['status']==1?'done':'';
        $statusText = $t['status']==1?'Done':'Pending';
        ?>
<tr>
    <td class="<?= $statusClass ?>"><?= htmlspecialchars($t['title']) ?></td>
    <td><span class="badge <?= $statusClass ?>"><?= $statusText ?></span></td>
    <td class="actions">
        <?php if($t['status']==0): ?><a href="action/mark.php?id=<?= $t['id'] ?>">✔ Done</a> | <?php endif; ?>
        <a href="task.php?delete=<?= $t['id'] ?>" onclick="return confirm('Delete?')">🗑 Delete</a> |
        <a href="action/edit.php?id=<?= $t['id'] ?>">✎ Edit</a>
    </td>
    <td><?= $t['prioritas']==1?'Yes':'No' ?></td>
    <td><?= date('Y-m-d H:i', strtotime($t['created_at'])) ?></td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="6" style="text-align:center;">No tasks found</td></tr>
<?php endif; ?>
</table>

</body>
</html>
